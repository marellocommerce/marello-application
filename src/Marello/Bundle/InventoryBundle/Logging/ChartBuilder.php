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

    /**
     * @param int           $product  Product ID
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

        $grouped = $this->groupByWarehouse($logItems);

        foreach ($grouped as $inventoryItemLabel => $logs) {
            $grouped[$inventoryItemLabel] = $this->valuesPerInterval($logs, $from, $to, $interval);
        }

        return $grouped;
    }

    /**
     * @param InventoryLog[] $logItems
     *
     * @return InventoryLog[][]
     */
    protected function groupByWarehouse($logItems)
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

    /**
     * @param InventoryLog[] $inventoryLogs
     * @param \DateTime      $from
     * @param \DateTime      $to
     * @param \DateInterval  $interval
     *
     * @return array
     */
    protected function valuesPerInterval($inventoryLogs, \DateTime $from, \DateTime $to, \DateInterval $interval)
    {
        /** @var InventoryLog $currentLog */
        $currentLog = reset($inventoryLogs);
        $values     = [];
        $nextValue  = $currentLog->getOldQuantity();

        $period = new \DatePeriod($from, $interval, $to);

        foreach ($period as $currentTime) {
            /*
             * Go trough logs until no more logs are present or current log is further in future as current time.
             */
            while (($currentLog !== false) && ($currentLog->getCreatedAt() <= $currentTime)) {
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
