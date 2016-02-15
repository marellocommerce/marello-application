<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;

class ChartBuilder
{
    /** @var Registry */
    protected $doctrine;

    /**
     * ChartBuilder constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param Product|int   $product  Product ID
     * @param \DateTime     $from     Start of period
     * @param \DateTime     $to       End of period
     * @param \DateInterval $interval Interval
     *
     * @return array
     */
    public function getChartData($product, \DateTime $from, \DateTime $to, \DateInterval $interval)
    {
        $logItems = $this->doctrine
            ->getRepository('MarelloInventoryBundle:InventoryLog')
            ->findByProductAndPeriod($product, $from, $to);

        $grouped = $this->groupByWarehouse($logItems, $product);

        foreach ($grouped as $warehouseLabel => $logs) {
            $grouped[$warehouseLabel] = $this->valuesPerInterval($logs, $from, $to, $interval);
        }

        return $grouped;
    }

    /**
     * @param InventoryLog[] $logItems
     * @param Product|int    $product
     *
     * @return \Marello\Bundle\InventoryBundle\Entity\InventoryLog[][]
     */
    protected function groupByWarehouse(array $logItems, $product)
    {
        $grouped = [];

        /** @var Warehouse[] $warehouses */
        $warehouses = $this->doctrine
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->findAll();

        /*
         * Initialize dataset for each warehouse.
         */
        foreach ($warehouses as $warehouse) {
            $grouped[$warehouse->getId()] = [];
        }

        /*
         * Put items into a group with appropriate warehouse.
         */
        foreach ($logItems as $inventoryLog) {
            $grouped[$inventoryLog->getInventoryItem()->getWarehouse()->getId()][] = $inventoryLog;
        }

        /*
         * Find last change for each warehouse without any change in given period.
         */
        foreach ($grouped as $warehouse => $logs) {
            if (!empty($logs)) {
                continue;
            }

            $lastChange = $this->doctrine
                ->getRepository('MarelloInventoryBundle:InventoryLog')
                ->findLastChangeForProductAndWarehouse($product, $warehouse);

            if ($lastChange) {
                $grouped[$warehouse] = [$lastChange];
            }
        }

        $named = array_combine(
            array_map(function (Warehouse $warehouse) {
                return $warehouse->getLabel();
            }, $warehouses),
            array_values($grouped)
        );

        return $named;
    }

    /**
     * @param InventoryLog[] $inventoryLogs
     * @param \DateTime      $from
     * @param \DateTime      $to
     * @param \DateInterval  $interval
     *
     * @return array
     */
    protected function valuesPerInterval(array $inventoryLogs, \DateTime $from, \DateTime $to, \DateInterval $interval)
    {
        /** @var InventoryLog $currentLog */
        $currentLog = reset($inventoryLogs);
        $values     = [];
        $nextValue  = $currentLog ? $currentLog->getOldQuantity() : 0;

        $period = new \DatePeriod($from, $interval, $to);

        foreach ($period as $currentTime) {
            /*
             * Go trough logs until no more logs are present or current log is further in future as current time.
             */
            while ($currentLog && ($currentLog->getCreatedAt() <= $currentTime)) {
                $nextValue  = $currentLog->getNewQuantity();
                $currentLog = next($inventoryLogs);
            }

            /*
             * Create record corresponding to current date.
             */
            $values[] = [
                'time'     => $currentTime->format(DATE_ISO8601),
                'quantity' => $nextValue,
            ];
        }

        return $values;
    }
}
