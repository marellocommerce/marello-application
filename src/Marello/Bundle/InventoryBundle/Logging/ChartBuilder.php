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

    /**
     * @param InventoryLog[] $logs
     * @param \DateTime      $from
     * @param \DateTime      $to
     * @param \DateInterval  $interval
     *
     * @return array
     */
    protected function valuesPerInterval($logs, \DateTime $from, \DateTime $to, \DateInterval $interval)
    {
        /** @var InventoryLog $log */
        $log      = reset($logs);
        $values   = [];
        $newValue = $log->getOldQuantity();

        $period   = new \DatePeriod($from, $interval, $to);

        foreach ($period as $currentTime) {
            while (($log !== false) && ($log->getCreatedAt() <= $currentTime)) {
                $newValue = $log->getNewQuantity();
                $log      = next($logs);
            }

            $values[] = [
                'time'     => $currentTime->format(DATE_ISO8601),
                'quantity' => $newValue,
            ];
        }

        return $values;
    }
}
