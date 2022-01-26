<?php

namespace Marello\Bundle\ReturnBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ReturnItemRepository extends EntityRepository
{
    public function getReturnQuantityByReason()
    {
        $stmt = $this->getEntityManager()->getConnection()->prepare(
            'SELECT 
                rri.reason_id AS returnReason,
                rri.product_sku AS productSku,
                rri.product_name AS productName,
                rri.returnedQty AS quantityReturned,
                ooi.orderedQty AS quantityOrdered,
                rri.returnedQty/ooi.orderedQty AS percentageReturned
            FROM (
                SELECT oi.product_sku, oi.product_name, ri.reason_id, CAST(SUM(ri.quantity) as float) AS returnedQty
                FROM marello_return_item ri
                INNER JOIN marello_order_order_item as oi on ri.order_item_id = oi.id
                GROUP BY oi.product_sku, oi.product_name, ri.reason_id
            ) AS rri
            INNER JOIN (
                SELECT oi.product_sku, SUM(oi.quantity) as orderedQty 
                FROM marello_order_order_item as oi
                group by oi.product_sku
            ) AS ooi ON rri.product_sku = ooi.product_sku'
        );
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }
}
