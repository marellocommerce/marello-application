<?php

namespace Marello\Bundle\PricingBundle\EventListener\Datagrid;

use Doctrine\ORM\Query\Expr;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class PricesDatagridListener
{
    const DATA_NAME = 'prices';
    const JOIN_ALIAS = 'pr';
    const DEFAULT_DATA_NAME = 'defaultPrice';
    const DEFAULT_JOIN_ALIAS = 'defpr';
    const SPECIAL_DATA_NAME = 'specialPrice';
    const SPECIAL_JOIN_ALIAS = 'sppr';
    const MSRP_DATA_NAME = 'msrpPrice';
    const MSRP_JOIN_ALIAS = 'mspr';

    /** @var string */
    protected $relatedEntityClass;

    /** @var Expr */
    protected $expressionBuilder;

    /**
     * @param string $relatedEntityClass
     */
    public function __construct(
        $relatedEntityClass
    ) {
        $this->relatedEntityClass = $relatedEntityClass;

        $this->expressionBuilder = new Expr();
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $this->addSelect($config);
        $this->addJoin($config);
        $this->addColumn($config);
        $this->addSorter($config);
        $this->addFilter($config);
    }

    /**
     * @param DatagridConfiguration $configuration
     * @return string
     * @throws \InvalidArgumentException when a root entity not found in the grid
     */
    protected function getAlias(DatagridConfiguration $configuration)
    {
        $rootAlias = $configuration->getOrmQuery()->getRootAlias();
        if (!$rootAlias) {
            throw new \InvalidArgumentException(
                sprintf(
                    'A root entity is missing for grid "%s"',
                    $configuration->getName()
                )
            );
        }

        return $rootAlias;
    }

    /**
     * @return string
     */
    protected function getDataName()
    {
        return self::DATA_NAME;
    }

    /**
     * @return string
     */
    protected function getJoinAlias()
    {
        return self::JOIN_ALIAS;
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addSelect(DatagridConfiguration $config)
    {
        $ormQuery = $config->getOrmQuery();
        $ormQuery
            ->addSelect(sprintf('sum(%s.value) as defaultPricesSum', self::DEFAULT_JOIN_ALIAS))
            ->addSelect(sprintf('sum(%s.value) as specialPricesSum', self::SPECIAL_JOIN_ALIAS))
            ->addSelect(sprintf('sum(%s.value) as msrpPricesSum', self::MSRP_JOIN_ALIAS));
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addJoin(DatagridConfiguration $config)
    {
        $ormQuery = $config->getOrmQuery();
        $ormQuery
            ->addLeftJoin(sprintf('%s.%s', $this->getAlias($config), self::DATA_NAME), $this->getJoinAlias())
            ->addLeftJoin(sprintf('%s.%s', $this->getJoinAlias(), self::DEFAULT_DATA_NAME), self::DEFAULT_JOIN_ALIAS)
            ->addLeftJoin(sprintf('%s.%s', $this->getJoinAlias(), self::SPECIAL_DATA_NAME), self::SPECIAL_JOIN_ALIAS)
            ->addLeftJoin(sprintf('%s.%s', $this->getJoinAlias(), self::MSRP_DATA_NAME), self::MSRP_JOIN_ALIAS);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addColumn(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(sprintf('[columns][%s]', 'defaultPrices'), [
            'label' => 'marello.pricing.assembledpricelist.default_price.plural_label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => 'MarelloPricingBundle:Datagrid/Property:defaultPrices.html.twig',
            'renderable' => false,
            'align' => 'right'
        ]);
        $config->offsetSetByPath(sprintf('[columns][%s]', 'specialPrices'), [
            'label' => 'marello.pricing.assembledpricelist.special_price.plural_label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => 'MarelloPricingBundle:Datagrid/Property:specialPrices.html.twig',
            'renderable' => false,
            'align' => 'right'
        ]);
        $config->offsetSetByPath(sprintf('[columns][%s]', 'msrpPrices'), [
            'label' => 'marello.pricing.assembledpricelist.msrp_price.plural_label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => 'MarelloPricingBundle:Datagrid/Property:msrpPrices.html.twig',
            'renderable' => false,
            'align' => 'right'
        ]);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addSorter(DatagridConfiguration $config)
    {
        $config
            ->offsetSetByPath(sprintf('[sorters][columns][%s]', 'defaultPrices'), ['data_name' => 'defaultPricesSum'])
            ->offsetSetByPath(sprintf('[sorters][columns][%s]', 'specialPrices'), ['data_name' => 'specialPricesSum'])
            ->offsetSetByPath(sprintf('[sorters][columns][%s]', 'msrpPrices'), ['data_name' => 'msrpPricesSum']);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addFilter(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(
            sprintf('[filters][columns][%s]', 'defaultPrices'),
            [
                'type' => 'number',
                'data_name' => self::DEFAULT_JOIN_ALIAS . '.value',
                'enabled' => false,
            ]
        );
        $config->offsetSetByPath(
            sprintf('[filters][columns][%s]', 'specialPrices'),
            [
                'type' => 'number',
                'data_name' => self::SPECIAL_JOIN_ALIAS . '.value',
                'enabled' => false,
            ]
        );
        $config->offsetSetByPath(
            sprintf('[filters][columns][%s]', 'msrpPrices'),
            [
                'type' => 'number',
                'data_name' => self::MSRP_JOIN_ALIAS . '.value',
                'enabled' => false,
            ]
        );
    }
}
