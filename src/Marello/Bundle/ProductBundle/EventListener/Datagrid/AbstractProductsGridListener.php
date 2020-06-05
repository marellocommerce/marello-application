<?php

namespace Marello\Bundle\ProductBundle\EventListener\Datagrid;

use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class AbstractProductsGridListener
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param OrmResultAfter $event
     */
    public function onResultAfter(OrmResultAfter $event)
    {
        /** @var ResultRecord[] $records */
        $records = $event->getRecords();
        $firstRecord = $records[0];
        if (!$firstRecord->getValue('product')) {
            $productIds = array_map(
                function (ResultRecord $record) {
                    return $record->getValue('id');
                },
                $records
            );
            /** @var Product[] $products */
            $products = $this->doctrineHelper
                ->getEntityRepository(Product::class)
                ->findBy(['id' => $productIds]);
            foreach ($records as $record) {
                foreach ($products as $product) {
                    $productId = $record->getValue('id');
                    if ($productId == $product->getId()) {
                        $record->addData(['product' => $product]);
                        break;
                    }
                }
            }
        }
    }
}