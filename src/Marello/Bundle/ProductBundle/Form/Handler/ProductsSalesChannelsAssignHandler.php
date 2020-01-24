<?php

namespace Marello\Bundle\ProductBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\ProductBundle\Async\ProductsAssignSalesChannelsProcessor;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductsSalesChannelsAssignHandler
{
    use RequestHandlerTrait;

    const FLUSH_BATCH_SIZE = 100;

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
     * @return bool
     * @throws \Exception
     */
    public function process()
    {
        $this->form->setData([
            'inset' => $this->request->query->get('inset'),
            'products' => $this->request->query->get('values'),
            'filters' => json_encode($this->request->query->get('filters')),
        ]);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);

            if ($this->form->isValid()) {
                $inset = $this->form->get('inset')->getData();
                $products = $this->form->get('products')->getData();
                $filters = json_decode($this->form->get('filters')->getData(), true);
                $addChannels = $this->form->get('addSalesChannels')->getData();

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
     * @param array $addChannels
     * @param string $inset
     * @param string|null $products
     * @param array|null $filters
     * @return array
     */
    private function onSuccess($addChannels, $inset, $products = null, $filters = null)
    {
        $isAllSelected = $this->isAllSelected($inset);
        $productIds = explode(',', $products);

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

            $countQueryBuilder = clone $queryBuilder;

            $result =  $countQueryBuilder
                ->resetDQLParts(['select', 'groupBy'])
                ->select(['COUNT(DISTINCT p.id) AS count'])
                ->getQuery()
                ->setFirstResult(0)
                ->setMaxResults(null)
                ->getOneOrNullResult();
            if ((int)$result['count'] <= self::FLUSH_BATCH_SIZE) {
                $this->processSmallData($queryBuilder, $addChannels);

                return [
                    'success' => true,
                    'type' => 'success',
                    'message' => 'marello.product.messages.success.sales_channels.assignment'
                ];
            } else {
                $this->processBigData($inset, $productIds, $filters, $addChannels);

                return [
                    'success' => true,
                    'type' => 'info',
                    'message' => 'marello.product.messages.success.sales_channels.assignment_started'
                ];
            }
        }

        return ['success' => false, 'message' => 'marello.product.messages.error.sales_channels.assignment'];
    }

    /**
     * @param string $inset
     * @return bool
     */
    protected function isAllSelected($inset)
    {
        return $inset === '0';
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param SalesChannel[] $salesChannels
     */
    private function processSmallData($queryBuilder, array $salesChannels)
    {
        $result = $queryBuilder
            ->getQuery()
            ->setFirstResult(0)
            ->setMaxResults(null)
            ->getResult();
        $iteration = 1;
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
     * @param int $inset
     * @param array|null $products
     * @param array|null $filters
     * @param SalesChannel[]|null $salesChannels
     */
    private function processBigData($inset, array $products = null, array $filters = null, array $salesChannels = null)
    {
        $channelIds = [];
        foreach ($salesChannels as $salesChannel) {
            $channelIds[] = $salesChannel->getCode();
        }
        $this->messageProducer->send(
            ProductsAssignSalesChannelsProcessor::TOPIC,
            [
                'inset' => $inset,
                'products' => $products,
                'filters' => $filters,
                'salesChannels' => $channelIds,
                'jobId' => md5(rand(1, 5))
            ]
        );
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        return $this->form->createView();
    }
}
