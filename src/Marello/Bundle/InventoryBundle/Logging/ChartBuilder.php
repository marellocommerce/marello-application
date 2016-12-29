<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Symfony\Component\Translation\TranslatorInterface;

class ChartBuilder
{
    /** @var Registry */
    protected $doctrine;

    /** @var DateHelper */
    protected $dateHelper;

    /** @var TranslatorInterface */
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
     * @param Product   $product
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return array
     */
    public function getChartData(Product $product, \DateTime $from, \DateTime $to)
    {
        $repository = $this->doctrine
            ->getRepository(StockLevel::class);

        $records            = $repository->getStockLevelsForProduct($product, $from, $to);
        $initialRecord      = $repository->getInitialStock($product, $from);
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
            $this->translator->trans('marello.inventory.stocklevel.stock.label')           => array_values(
                array_map(function ($value) {
                    return ['time' => $value['date'], 'inventory' => $value['inventory']];
                }, $dates)
            ),
            $this->translator->trans('marello.inventory.stocklevel.allocated_stock.label') => array_values(
                array_map(function ($value) {
                    return ['time' => $value['date'], 'inventory' => $value['allocatedInventory']];
                }, $dates)
            ),
            $this->translator->trans('marello.inventory.stocklevel.virtual_stock.label')   => array_values(
                array_map(function ($value) {
                    return ['time' => $value['date'], 'inventory' => $value['inventory'] - $value['allocatedInventory']];
                }, $dates)
            ),
        ];

        return $data;
    }
}
