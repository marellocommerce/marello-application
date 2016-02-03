<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;

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

    public function getChartData($product, $from, $to, $interval = '1 day')
    {
        $logItems = $this->doctrine
            ->getRepository('MarelloInventoryBundle:InventoryLog')
            ->findByProductAndPeriod($product, $from, $to);

        $grouped = $this->groupByInventoryItem($logItems);

        foreach ($grouped as $inventoryItemLabel => $logs) {
            $grouped[$inventoryItemLabel] = $this->valuesPerInterval($logs, $from, $to, $interval);
        }

        return $grouped;
    }

    protected function groupByInventoryItem($logItems)
    {
        $grouped = [];

        array_map(
            function (InventoryLog $inventoryLog) use (&$grouped) {
                $inventoryItemLabel = $inventoryLog->getInventoryItem()->getWarehouse()->getLabel();

                if (!array_key_exists($inventoryItemLabel, $grouped)) {
                    $grouped[$inventoryItemLabel] = [];
                }

                $grouped[$inventoryItemLabel][] = $inventoryLog;
            },
            $logItems
        );

        return $grouped;
    }

    protected function valuesPerInterval($logs, $from, $to, $interval)
    {
        /** @var \DateTime $currentTime */
        $currentTime = clone $from;
        /** @var InventoryLog $log */
        $log      = reset($logs);
        $values   = [];
        $newValue = $log->getOldQuantity();

        while ($currentTime <= $to) {
            while (($log !== false) && ($log->getCreatedAt() <= $currentTime)) {
                $newValue = $log->getNewQuantity();
                $log      = next($logs);
            }

            $values[] = [
                'time' => $currentTime->format(DATE_ISO8601),
                'quantity' => $newValue
            ];

            $currentTime->modify('+ ' . $interval);
        }

        return $values;
    }
}
