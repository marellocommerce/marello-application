<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

use Marello\Bundle\PurchaseOrderBundle\Provider\PurchaseOrderCandidatesProvider;

class PurchaseOrderItemCandidatesGridListener
{
    /** @var PurchaseOrderCandidatesProvider $purchaseOrderCandidatesProvider */
    protected $purchaseOrderCandidatesProvider;

    public function __construct(PurchaseOrderCandidatesProvider $purchaseOrderCandidatesProvider)
    {
        $this->purchaseOrderCandidatesProvider = $purchaseOrderCandidatesProvider;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBeforeCandidatesGrid(BuildBefore $event)
    {
        $config = $event->getConfig();

        $productIdsToExclude = $this->purchaseOrderCandidatesProvider->getProductsIdsInPendingPurchaseOrders();
        if ($productIdsToExclude) {
            $config->offsetAddToArrayByPath('source.query.where.and', [
                sprintf('p.id NOT IN (%s)', $productIdsToExclude)
            ]);
        }
    }
}
