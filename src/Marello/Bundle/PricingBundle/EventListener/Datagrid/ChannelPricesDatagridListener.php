<?php

namespace Marello\Bundle\PricingBundle\EventListener\Datagrid;

use Doctrine\ORM\Query\Expr;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class ChannelPricesDatagridListener
{
    const DATA_NAME = 'channelPrices';
    const JOIN_ALIAS = 'cpr';
    const CHANNEL_DATA_NAME = 'channel';
    const CHANNEL_JOIN_ALIAS = 'ch';
    const DEFAULT_DATA_NAME = 'defaultPrice';
    const DEFAULT_JOIN_ALIAS = 'defcpr';
    const SPECIAL_DATA_NAME = 'specialPrice';
    const SPECIAL_JOIN_ALIAS = 'spcpr';

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
            ->addSelect(
                sprintf(
                    'GROUP_CONCAT(
                        DISTINCT CONCAT_WS(\'|\', %1$s.name, %2$s.value, %2$s.currency) SEPARATOR \';\'
                    ) as defaultChannelPrices',
                    self::CHANNEL_JOIN_ALIAS,
                    self::DEFAULT_JOIN_ALIAS
                )
            )
            ->addSelect(
                sprintf(
                    'GROUP_CONCAT(
                        DISTINCT CONCAT_WS(\'|\', %1$s.name, %2$s.value, %2$s.currency) SEPARATOR \';\'
                    ) as specialChannelPrices',
                    self::CHANNEL_JOIN_ALIAS,
                    self::SPECIAL_JOIN_ALIAS
                )
            )
            ->addSelect(sprintf('sum(%s.value) as defaultChannelPricesSum', self::DEFAULT_JOIN_ALIAS))
            ->addSelect(sprintf('sum(%s.value) as specialChannelPricesSum', self::SPECIAL_JOIN_ALIAS));
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addJoin(DatagridConfiguration $config)
    {
        $ormQuery = $config->getOrmQuery();
        $ormQuery
            ->addLeftJoin(sprintf('%s.%s', $this->getAlias($config), self::DATA_NAME), $this->getJoinAlias())
            ->addLeftJoin(sprintf('%s.%s', $this->getJoinAlias(), self::CHANNEL_DATA_NAME), self::CHANNEL_JOIN_ALIAS)
            ->addLeftJoin(sprintf('%s.%s', $this->getJoinAlias(), self::DEFAULT_DATA_NAME), self::DEFAULT_JOIN_ALIAS)
            ->addLeftJoin(sprintf('%s.%s', $this->getJoinAlias(), self::SPECIAL_DATA_NAME), self::SPECIAL_JOIN_ALIAS);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addColumn(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(sprintf('[columns][%s]', 'defaultChannelPrices'), [
            'label' => 'marello.pricing.assembledchannelpricelist.default_price.plural_label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => 'MarelloPricingBundle:Datagrid/Property:defaultChannelPrices.html.twig',
            'renderable' => false,
            'align' => 'right'
        ]);
        $config->offsetSetByPath(sprintf('[columns][%s]', 'specialChannelPrices'), [
            'label' => 'marello.pricing.assembledchannelpricelist.special_price.plural_label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => 'MarelloPricingBundle:Datagrid/Property:specialChannelPrices.html.twig',
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
            ->offsetSetByPath(
                sprintf('[sorters][columns][%s]', 'defaultChannelPrices'),
                ['data_name' => 'defaultChannelPricesSum']
            )
            ->offsetSetByPath(
                sprintf('[sorters][columns][%s]', 'specialChannelPrices'),
                ['data_name' => 'specialChannelPricesSum']
            );
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addFilter(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(
            sprintf('[filters][columns][%s]', 'defaultChannelPrices'),
            [
                'type' => 'number',
                'data_name' => self::DEFAULT_JOIN_ALIAS . '.value',
                'enabled' => false,
            ]
        );
        $config->offsetSetByPath(
            sprintf('[filters][columns][%s]', 'specialChannelPrices'),
            [
                'type' => 'number',
                'data_name' => self::SPECIAL_JOIN_ALIAS . '.value',
                'enabled' => false,
            ]
        );
    }
}
