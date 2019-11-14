<?php

namespace Marello\Bundle\SalesBundle\EventListener\Datagrid;

use Doctrine\ORM\Query\Expr;
use Marello\Bundle\SalesBundle\Provider\SalesChannelsChoicesProviderInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class SalesChannelsDatagridListener
{
    const DATA_NAME = 'channels';
    const JOIN_ALIAS = 'sc';

    /** @var SalesChannelsChoicesProviderInterface */
    protected $salesChannelsChoicesProvider;

    /** @var string */
    protected $relatedEntityClass;

    /** @var Expr */
    protected $expressionBuilder;

    /**
     * @param string $relatedEntityClass
     * @param SalesChannelsChoicesProviderInterface $salesChannelsChoicesProvider
     */
    public function __construct(
        $relatedEntityClass,
        SalesChannelsChoicesProviderInterface $salesChannelsChoicesProvider
    ) {
        $this->relatedEntityClass = $relatedEntityClass;
        $this->salesChannelsChoicesProvider = $salesChannelsChoicesProvider;

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
    protected function getColumnLabel()
    {
        return 'marello.product.channels.label';
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
        $config->getOrmQuery()->addSelect(
            sprintf('count(%s.code) AS channelsCount', $this->getJoinAlias())
        );
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addJoin(DatagridConfiguration $config)
    {
        $config->getOrmQuery()->addLeftJoin(
            $this->getAlias($config).'.channels',
            $this->getJoinAlias()
        );
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addColumn(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(sprintf('[columns][%s]', $this->getDataName()), [
            'label' => $this->getColumnLabel(),
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => 'MarelloSalesBundle:SalesChannel/Datagrid/Property:channels.html.twig',
            'renderable' => false
        ]);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addSorter(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(
            sprintf('[sorters][columns][%s]', $this->getDataName()),
            ['data_name' => 'channelsCount']
        );
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addFilter(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(
            sprintf('[filters][columns][%s]', $this->getDataName()),
            [
                'type' => 'choice',
                'data_name' => $this->getJoinAlias() . '.code',
                'enabled' => false,
                'options' => [
                    'field_options' => [
                        'multiple' => true,
                        'choices' => $this->salesChannelsChoicesProvider->getChannels()
                    ]
                ]
            ]
        );
    }
}
