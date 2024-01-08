<?php

namespace Marello\Bundle\ProductBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Oro\Bundle\EmailBundle\Form\Model\Factory;
use Oro\Bundle\EmailBundle\Sender\EmailModelSender;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Marello\Bundle\ProductBundle\Async\Topic\ProductsAssignSalesChannelsTopic;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class ProductsAssignSalesChannelsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    const FLUSH_BATCH_SIZE = 100;

    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage,
        private Factory $emailModelFactory,
        private EmailModelSender $emailModelSender
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [ProductsAssignSalesChannelsTopic::getName()];
    }

    /**
     * @param MessageInterface $message
     * @param SessionInterface $session
     * @return string
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $data = JSON::decode($message->getBody());

        $products = $data['products'];
        $salesChannels = $data['salesChannels'];
        $salesChannels = $this->entityManager->getRepository(SalesChannel::class)->findBy(['id' => $salesChannels]);

        if (!empty($products) && !empty($salesChannels)) {
            /** @var ProductRepository $productRepo */
            $productRepo = $this->entityManager->getRepository(Product::class);
            $products = $productRepo->findBy(['id' => $products]);
            try {
                $modifiedProducts = [];
                /** @var Product $entity */
                foreach ($products as $entity) {
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

                    if ((count($modifiedProducts) % self::FLUSH_BATCH_SIZE) === 0) {
                        $this->entityManager->flush();
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
     * @param array $assignedSalesChannels
     * @param array $modifiedProducts
     */
    private function sendMail(array $assignedSalesChannels, array $modifiedProducts): void
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
            )
            ->setOrganization($currentUser->getOrganization());

        $this->emailModelSender->send($emailModel);
    }
}
