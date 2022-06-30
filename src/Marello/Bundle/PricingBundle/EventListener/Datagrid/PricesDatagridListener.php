<?php

namespace Marello\Bundle\PricingBundle\EventListener\Datagrid;

use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class PricesDatagridListener
{
    const DEFAULT_PRICES_COLUMN = 'defaultPrices';
    const SPECIAL_PRICES_COLUMN = 'specialPrices';
    const MSRP_PRICES_COLUMN = 'msrpPrices';

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
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $config->offsetSetByPath(sprintf('[columns][%s]', self::DEFAULT_PRICES_COLUMN), [
            'label' => 'marello.pricing.assembledpricelist.default_price.plural_label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => '@MarelloPricing/Datagrid/Property/defaultPrices.html.twig',
            'renderable' => false,
            'align' => 'right'
        ]);
        $config->offsetSetByPath(sprintf('[columns][%s]', self::SPECIAL_PRICES_COLUMN), [
            'label' => 'marello.pricing.assembledpricelist.special_price.plural_label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => '@MarelloPricing/Datagrid/Property/specialPrices.html.twig',
            'renderable' => false,
            'align' => 'right'
        ]);
        $config->offsetSetByPath(sprintf('[columns][%s]', self::MSRP_PRICES_COLUMN), [
            'label' => 'marello.pricing.assembledpricelist.msrp_price.plural_label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => '@MarelloPricing/Datagrid/Property/msrpPrices.html.twig',
            'renderable' => false,
            'align' => 'right'
        ]);
    }

    /**
     * @param OrmResultAfter $event
     */
    public function onResultAfter(OrmResultAfter $event)
    {
        /** @var ResultRecord[] $records */
        $records = $event->getRecords();

        $productIds = array_map(
            function (ResultRecord $record) {
                return $record->getValue('id');
            },
            $records
        );

        $this->addProductPrices($productIds, $records);
    }

    /**
     * @param array $productIds
     * @param array|ResultRecord[] $records
     */
    protected function addProductPrices(array $productIds, array $records)
    {
        $groupedPrices = $this->getPrices($productIds);

        foreach ($records as $record) {
            $priceLists = [];
            $productId = $record->getValue('id');

            if (array_key_exists($productId, $groupedPrices)) {
                /** @var AssembledPriceList[] $priceLists */
                $priceLists = $groupedPrices[$productId];
            }
            $data = [];
            foreach ($priceLists as $priceList) {
                if ($priceList->getDefaultPrice()) {
                    $data[self::DEFAULT_PRICES_COLUMN][] = $priceList->getDefaultPrice();
                }
                if ($priceList->getSpecialPrice()) {
                    $data[self::SPECIAL_PRICES_COLUMN][] = $priceList->getSpecialPrice();
                }
                if ($priceList->getMsrpPrice()) {
                    $data[self::MSRP_PRICES_COLUMN][] = $priceList->getMsrpPrice();
                }
            }

            $record->addData($data);
        }
    }

    /**
     * @param array $productIds
     * @return array
     */
    protected function getPrices(array $productIds)
    {
        $prices = $this->doctrineHelper
            ->getEntityRepository(AssembledPriceList::class)
            ->findBy(['product' => $productIds]);

        $result = [];
        foreach ($prices as $price) {
            $result[$price->getProduct()->getId()][] = $price;
        }

        return $result;
    }
}
