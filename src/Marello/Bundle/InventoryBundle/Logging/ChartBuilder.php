<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Symfony\Component\Translation\TranslatorInterface;

class ChartBuilder
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * ChartBuilder constructor.
     *
     * @param Registry            $doctrine
     * @param DateHelper          $dateHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(Registry $doctrine, DateHelper $dateHelper, TranslatorInterface $translator)
    {
        $this->doctrine   = $doctrine;
        $this->dateHelper = $dateHelper;
        $this->translator = $translator;
    }

    /**
     * Returns data in format ready for inventory chart.
     *
     * @param InventoryItem $inventoryItem
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return array
     */
    public function getChartData(InventoryItem $inventoryItem, \DateTime $from, \DateTime $to)
    {
        $repository = $this->doctrine
            ->getRepository(InventoryLevelLogRecord::class);

        $records            = $repository->getInventoryLogRecordsForItem($inventoryItem, $from, $to);
        $initialRecord      = $repository->getInitialInventory($inventoryItem, $from);
        $inventory          = $initialRecord['inventory'];
        $allocatedInventory = $initialRecord['allocatedInventory'];

        $dates  = $this->dateHelper->getDatePeriod($from, $to);
        $record = reset($records);

        foreach ($dates as &$date) {
            if ($record !== false && $record['date'] === $date['date']) {
                $inventory += $record['inventory'];
                $allocatedInventory += $record['allocatedInventory'];

                $record = next($records);
            }

            $date['inventory']          = $inventory;
            $date['allocatedInventory'] = $allocatedInventory;
        }

        $data = [
            $this->translator->trans('marello.inventory.inventorylevel.inventory.label') => array_values(
                array_map(function ($value) {
                    return [
                        'time' => $value['date'],
                        'inventory' => $value['inventory']
                    ];
                }, $dates)
            ),
            $this->translator->trans('marello.inventory.inventorylevel.allocated_inventory.label') => array_values(
                array_map(function ($value) {
                    return [
                        'time' => $value['date'],
                        'inventory' => $value['allocatedInventory']
                    ];
                }, $dates)
            ),
            $this->translator->trans('marello.inventory.inventorylevel.virtual_inventory.label') => array_values(
                array_map(function ($value) {
                    return [
                        'time' => $value['date'],
                        'inventory' => $value['inventory'] - $value['allocatedInventory']
                    ];
                }, $dates)
            ),
        ];

        return $data;
    }
}
