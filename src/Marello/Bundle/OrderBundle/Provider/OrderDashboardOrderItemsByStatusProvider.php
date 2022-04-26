<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Marello\Bundle\OrderBundle\Entity\Repository\OrderItemRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OrderDashboardOrderItemsByStatusProvider
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var WidgetProviderFilterManager */
    protected $widgetProviderFilter;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /** @var  CurrencyQueryBuilderTransformerInterface */
    protected $qbTransformer;

    /**
     * @param ManagerRegistry $doctrine
     * @param AclHelper $aclHelper
     * @param WidgetProviderFilterManager $widgetProviderFilter
     * @param DateFilterProcessor $processor
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     */
    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        WidgetProviderFilterManager $widgetProviderFilter,
        DateFilterProcessor $processor,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->registry             = $doctrine;
        $this->aclHelper            = $aclHelper;
        $this->widgetProviderFilter = $widgetProviderFilter;
        $this->dateFilterProcessor  = $processor;
        $this->qbTransformer        = $qbTransformer;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     *
     * @return array
     */
    public function getOrderItemsGroupedByStatus(WidgetOptionBag $widgetOptions)
    {
        $dateRange = $widgetOptions->get('dateRange');

        /**
         * Excluded statuses will be filtered from result in method `formatResult` below.
         * Due to performance issues with `NOT IN` clause in database.
         */
        $statuses = $widgetOptions->get('statuses') ? : [];

        /** @var OrderItemRepository $orderitemRepository */
        $orderitemRepository = $this->registry->getRepository('MarelloOrderBundle:Order');
        $qb = $orderitemRepository->createQueryBuilder('o')
            ->select('IDENTITY (oi.status) status, COUNT(oi.id) as quantity')
            ->andWhere('IDENTITY (oi.status) IS NOT NULL')
            ->innerJoin('o.items', 'oi')
            ->groupBy('oi.status')
            ->orderBy('quantity', 'DESC');

        $this->dateFilterProcessor->applyDateRangeFilterToQuery($qb, $dateRange, 'o.createdAt');
        $this->widgetProviderFilter->filter($qb, $widgetOptions);
        $result = $this->aclHelper->apply($qb)->getArrayResult();

        return $this->formatResult($result, $statuses, 'quantity');
    }

    /**
     * @param array    $result
     * @param string[] $statuses
     * @param string   $orderBy
     *
     * @return array
     */
    protected function formatResult($result, $statuses, $orderBy)
    {
        $resultStatuses = array_flip(array_column($result, 'status', null));

        foreach ($this->getAvailableItemStatuses() as $statusKey => $statusLabel) {
            $resultIndex = isset($resultStatuses[$statusKey]) ? $resultStatuses[$statusKey] : null;
            if (!empty($statuses)) {
                if (!in_array($statusKey, $statuses)) {
                    if (null !== $resultIndex) {
                        unset($result[$resultIndex]);
                    }
                    continue;
                }
            }

            if (null !== $resultIndex) {
                $result[$resultIndex]['label'] = $statusLabel;
            } else {
                $result[] = [
                    'status' => $statusKey,
                    'label'  => $statusLabel,
                    $orderBy => 0
                ];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getAvailableItemStatuses()
    {
        /** @var EnumValueRepository $statusesRepository */
        $statusesRepository = $this->registry->getRepository(
            ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS)
        );
        $statuses = $statusesRepository->createQueryBuilder('s')
            ->select('s.id, s.name')
            ->getQuery()
            ->getArrayResult();

        return array_column($statuses, 'name', 'id');
    }
}
