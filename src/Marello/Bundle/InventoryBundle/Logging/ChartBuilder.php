<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Repository\InventoryLevelLogRecordRepository;
use Marello\Bundle\InventoryBundle\Model\InventoryTotalCalculator;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChartBuilder
{
    /**
     * @var InventoryLevelLogRecordRepository
     */
    protected $logRecordRepository;

    /**
     * @var InventoryTotalCalculator
     */
    protected $inventoryCalculator;

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
     * @param InventoryLevelLogRecordRepository $logRecordRepository
     * @param InventoryTotalCalculator $calculator
     * @param DateHelper $dateHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        InventoryLevelLogRecordRepository $logRecordRepository,
        InventoryTotalCalculator $calculator,
        DateHelper $dateHelper,
        TranslatorInterface $translator
    ) {
        $this->logRecordRepository = $logRecordRepository;
        $this->inventoryCalculator = $calculator;
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
        $records            = $this->getResultRecords($inventoryItem, $from, $to);
        $inventory          = $this->inventoryCalculator->getTotalInventoryQty($inventoryItem);
        $allocatedInventory = $this->inventoryCalculator->getTotalAllocatedInventoryQty($inventoryItem);

        // put dates in reverse order for correctly 'calculating' the totals
        $reverseDates = array_reverse($this->dateHelper->getDatePeriod($from, $to), true);
        foreach ($reverseDates as &$date) {
            foreach ($records as $record) {
                if ($record !== false && $record['date'] === $date['date']) {
                    $inventory -= $record['inventory'];
                    $allocatedInventory -= $record['allocatedInventory'];
                }
            }

            $date['inventory']          = $inventory;
            $date['allocatedInventory'] = $allocatedInventory;
        }

        // switch back date reversal, for correctly displaying the time line
        $dates = array_reverse($reverseDates, true);
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

    /**
     * Get result records with 'adjusted' date time for later processing of the records
     * 'date time' is adjusted because InventoryLogLevels show the inventory movement of that same day while
     * we need to calculate the different between the levels of the day before and the same day
     * record['inventory'] = 80, means it changed 80 today compared to yesterday. In order to get the correct
     * level for display of the previous day, we change the datetime just for the result record temporary in the records
     * array and let it calculate with the previous day instead of the same day. ¯\_(ツ)_/¯
     * @param $inventoryItem
     * @param $from
     * @param $to
     * @return array
     */
    protected function getResultRecords($inventoryItem, $from, $to)
    {
        $records = $this->logRecordRepository->getInventoryLogRecordsForItem($inventoryItem, $from, $to);
        $resultRecords = [];
        foreach ($records as $k => $record) {
            $record['date'] = $this->modifyDateTimeString($record['date']);
            $resultRecords[$k] = $record;
        }

        return $resultRecords;
    }

    /**
     * modify date time string to the day before
     * @param string $dateTime
     * @return string
     */
    protected function modifyDateTimeString($dateTime)
    {
        $dateTime = new \DateTime($dateTime);
        return $dateTime->modify('- 1 days')->format('Y-m-d');
    }
}
