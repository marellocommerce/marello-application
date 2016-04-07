<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
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
            ->getRepository(InventoryLog::class);

        $records           = $repository->getQuantitiesForProduct($product, $from, $to);
        $initialRecord     = $repository->getInitialQuantities($product, $from);
        $quantity          = $initialRecord['quantity'];
        $allocatedQuantity = $initialRecord['allocatedQuantity'];

        $dates  = $this->dateHelper->getDatePeriod($from, $to);
        $record = reset($records);

        foreach ($dates as &$date) {
            if ($record !== false && $record['date'] === $date['date']) {
                $quantity += $record['quantity'];
                $allocatedQuantity += $record['allocatedQuantity'];

                $record = next($records);
            }

            $date['quantity']          = $quantity;
            $date['allocatedQuantity'] = $allocatedQuantity;
        }

        $data = [
            $this->translator->trans('marello.inventory.inventoryitem.quantity.label')          => array_values(
                array_map(function ($value) {
                    return ['time' => $value['date'], 'quantity' => $value['quantity']];
                }, $dates)
            ),
            $this->translator->trans('marello.inventory.inventoryitem.allocated_quantity.label') => array_values(
                array_map(function ($value) {
                    return ['time' => $value['date'], 'quantity' => $value['allocatedQuantity']];
                }, $dates)
            ),
            $this->translator->trans('marello.inventory.inventoryitem.virtual_quantity.label')   => array_values(
                array_map(function ($value) {
                    return ['time' => $value['date'], 'quantity' => $value['quantity'] - $value['allocatedQuantity']];
                }, $dates)
            ),
        ];

        return $data;
    }
}
