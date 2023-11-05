<?php

namespace Marello\Bundle\ProductBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Marello\Bundle\ProductBundle\Async\Topic\ProductsAssignSalesChannelsTopic;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class ProductsSalesChannelsAssignHandler
{
    use RequestHandlerTrait;
    use JobIdGenerationTrait;

    /** @var int size that will determine wether the products should be saved immediately or send to the queue  */
    const FLUSH_BATCH_SIZE = 100;

    /** @var int max size of product ids per message to prevent having a single big message */
    const MESSAGE_PRODUCT_ID_SIZE = 100;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Manager
     */
    protected $datagridManager;

    /**
     * @var MessageProducerInterface
     */
    protected $messageProducer;

    /**
     * @param FormInterface $form
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     * @param Manager $datagridManager
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        Manager $datagridManager,
        MessageProducerInterface $messageProducer
    ) {
        $this->form = $form;
        $this->request = $requestStack->getCurrentRequest();
        $this->entityManager = $entityManager;
        $this->datagridManager = $datagridManager;
        $this->messageProducer = $messageProducer;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function process(): array
    {
        $filtersFromRequest = null;
        if (null !== $this->request->query->get('filters')) {
            $filtersFromRequest = JSON::encode($this->request->query->get('filters'));
        }

        $this->form->setData([
            'inset' => $this->request->query->get('inset'),
            'products' => $this->request->query->get('values'),
            'filters' => $filtersFromRequest
        ]);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);

            if ($this->form->isValid()) {
                $inset = $this->form->get('inset')->getData();
                $products = $this->form->get('products')->getData();
                $addChannels = $this->form->get('addSalesChannels')->getData();
                $filters = null;
                if (null !== $this->form->get('filters')->getData()) {
                    JSON::decode($this->form->get('filters')->getData());
                }
                return $this->onSuccess(
                    $addChannels,
                    $inset,
                    $products,
                    $filters
                );
            }
        }

        return ['success' => false, 'message' => 'marello.product.messages.error.sales_channels.assignment'];
    }

    /**
     * @param $addChannels
     * @param $inset
     * @param null $products
     * @param null $filters
     * @return array
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    private function onSuccess($addChannels, $inset, $products = null, $filters = null): array
    {
        $isAllSelected = $this->isAllSelected($inset);
        $productIds = array_filter(explode(',', $products));

        if (!empty($productIds) || $isAllSelected) {
            $grid = $this->datagridManager->getDatagridByRequestParams(
                'marello-products-grid',
                ['_filter' => $filters]
            );
            /** @var OrmDatasource $dataSource */
            $dataSource = $grid->getAcceptedDatasource();
            $queryBuilder = $dataSource->getQueryBuilder();

            if (!$isAllSelected) {
                $queryBuilder->andWhere($queryBuilder->expr()->in('p.id', $productIds));
            } elseif ($productIds) {
                $queryBuilder->andWhere($queryBuilder->expr()->notIn('p.id', $productIds));
            }

            $queryResult = $queryBuilder
                ->getQuery()
                ->setFirstResult(0)
                ->setMaxResults(null)
                ->getArrayResult();

            if ((int)count($queryResult) <= self::FLUSH_BATCH_SIZE) {
                $this->processSmallData($queryResult, $addChannels);

                return [
                    'success' => true,
                    'type' => 'success',
                    'message' => 'marello.product.messages.success.sales_channels.assignment'
                ];
            }

            $this->processBigData($queryResult, $addChannels);
            return [
                'success' => true,
                'type' => 'info',
                'message' => 'marello.product.messages.success.sales_channels.assignment_started'
            ];
        }

        return ['success' => false, 'message' => 'marello.product.messages.error.sales_channels.assignment'];
    }

    /**
     * @param string $inset
     * @return bool
     */
    protected function isAllSelected($inset): bool
    {
        return $inset === '0';
    }

    /**
     * @param array $queryResult
     * @param SalesChannel[] $salesChannels
     */
    private function processSmallData(array $queryResult, array $salesChannels): void
    {
        $productIds = $this->getProductIdsFromResult($queryResult);
        $products = $this->entityManager
            ->getRepository(Product::class)
            ->findBy(['id' => $productIds]);

        $iteration = 1;
        /** @var Product $product */
        foreach ($products as $product) {
            $addedChannels = 0;
            foreach ($salesChannels as $salesChannel) {
                if (!$product->hasChannel($salesChannel)) {
                    $product->addChannel($salesChannel);
                    $this->entityManager->persist($product);
                    $addedChannels++;
                }
            }

            if (($iteration % self::FLUSH_BATCH_SIZE) === 0) {
                $this->entityManager->flush();
            }
            if ($addedChannels > 0) {
                $iteration++;
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param array $queryResult
     * @param SalesChannel[] $salesChannels
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    private function processBigData(array $queryResult, array $salesChannels): void
    {
        $channelIds = array_map(
            static function (SalesChannel $channel) {
                return $channel->getId();
            },
            $salesChannels
        );

        $productIds = $this->getProductIdsFromResult($queryResult);
        if (count($productIds) > self::MESSAGE_PRODUCT_ID_SIZE) {
            $chunks = array_chunk($productIds, self::MESSAGE_PRODUCT_ID_SIZE);
            foreach ($chunks as $chunk) {
                $this->sendProductsToMessageQueue($chunk, $channelIds);
            }
        } else {
            $this->sendProductsToMessageQueue($productIds, $channelIds);
        }
    }

    /**
     * @param array $productIds
     * @param array $channelIds
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    private function sendProductsToMessageQueue(array $productIds, array $channelIds)
    {
        $this->messageProducer->send(
            ProductsAssignSalesChannelsTopic::getName(),
            [
                'products' => $productIds,
                'salesChannels' => $channelIds,
                'jobId' => $this->generateJobId(implode($productIds))
            ]
        );
    }

    /**
     * Returns form view instance
     *
     * @return FormView
     */
    public function getFormView(): FormView
    {
        return $this->form->createView();
    }

    /**
     * @param array $result
     * @return array
     */
    private function getProductIdsFromResult(array $result): array
    {
        return array_map(
            static function ($entityAsArray) {
                if (array_key_exists('id', $entityAsArray)) {
                    return $entityAsArray['id'];
                }
                return null;
            },
            $result
        );
    }
}
