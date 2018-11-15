<?php

namespace Marello\Bundle\ProductBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\EmailBundle\Form\Model\Factory;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProductsAssignSalesChannelsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    const TOPIC = 'marello_product.assign_sales_channels_to_products';

    const FLUSH_BATCH_SIZE = 100;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Manager
     */
    private $datagridManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Factory
     */
    private $emailModelFactory;

    /**
     * @var Processor
     */
    private $emailProcessor;

    /**
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param Manager $datagridManager
     * @param TokenStorageInterface $tokenStorage,
     * @param Factory $emailModelFactory,
     * @param Processor $emailProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        Manager $datagridManager,
        TokenStorageInterface $tokenStorage,
        Factory $emailModelFactory,
        Processor $emailProcessor
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->datagridManager = $datagridManager;
        $this->tokenStorage = $tokenStorage;
        $this->emailModelFactory = $emailModelFactory;
        $this->emailProcessor = $emailProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [self::TOPIC];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());

        $inset = $data['inset'];
        $products = $data['products'];
        $filters = $data['filters'];
        $salesChannels = $data['salesChannels'];

        $isAllSelected = $inset === '0';
        $salesChannels = $this->entityManager->getRepository(SalesChannel::class)->findBy(['code' => $salesChannels]);

        if (!empty($products) || $isAllSelected) {
            $grid = $this->datagridManager->getDatagridByRequestParams(
                'marello-products-grid',
                ['_filter' => $filters]
            );
            /** @var OrmDatasource $dataSource */
            $dataSource = $grid->getAcceptedDatasource();
            $queryBuilder = $dataSource->getQueryBuilder();

            if (!$isAllSelected) {
                $queryBuilder->andWhere($queryBuilder->expr()->in('p.id', $products));
            } elseif ($products) {
                $queryBuilder->andWhere($queryBuilder->expr()->notIn('p.id', $products));
            }

            $result = $queryBuilder
                ->getQuery()
                ->setFirstResult(0)
                ->setMaxResults(null)
                ->getResult();

            try {
                $iteration = 1;
                $modifiedProducts = [];
                foreach ($result as $entity) {
                    /** @var Product $entity */
                    $entity = $entity[0];
                    $addedChannels = 0;
                    foreach ($salesChannels as $salesChannel) {
                        if (!$entity->hasChannel($salesChannel)) {
                            $entity->addChannel($salesChannel);
                            $addedChannels++;
                        }
                    }
                    if ($addedChannels > 0) {
                        $this->entityManager->persist($entity);
                        $modifiedProducts[] = $entity;
                    }

                    if (($iteration % self::FLUSH_BATCH_SIZE) === 0) {
                        $this->entityManager->flush();
                    }
                    if ($addedChannels > 0) {
                        $iteration++;
                    }
                }
                $this->entityManager->flush();
                $this->sendMail($salesChannels, $modifiedProducts);
            } catch (\Exception $e) {
                $this->logger->error(
                    'Unexpected exception occurred during Sales Channels Assignment',
                    ['exception' => $e]
                );

                return self::REJECT;
            }

            return self::ACK;
        }

        return self::REJECT;
    }

    /**
     * @param SalesChannel[] $assignedSalesChannels
     * @param Product[] $modifiedProducts
     */
    private function sendMail(array $assignedSalesChannels, array $modifiedProducts)
    {
        /** @var User $currentUser */
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $emailModel = $this->emailModelFactory->getEmail();

        $salesChannels = array_map(
            function (SalesChannel $channel) {
                return $channel->getName();
            },
            $assignedSalesChannels
        );

        $products = array_map(
            function (Product $product) {
                return $product->getSku();
            },
            $modifiedProducts
        );

        $emailModel
            ->setType('html')
            ->setFrom($currentUser->getEmail())
            ->setTo([$currentUser->getEmail()])
            ->setSubject('Sales Channels to Products assignment')
            ->setBody(
                sprintf(
                    "Sales Channels:<br> %s <br><br>Have been assigned to Products:<br> %s",
                    implode('<br>', $salesChannels),
                    implode('<br>', $products)
                )
            );
        $this->emailProcessor->process($emailModel);
    }
}
