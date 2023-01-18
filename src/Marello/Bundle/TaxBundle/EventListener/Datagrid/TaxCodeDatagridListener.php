<?php

namespace Marello\Bundle\TaxBundle\EventListener\Datagrid;

use Doctrine\ORM\Query\Expr;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class TaxCodeDatagridListener
{
    const DATA_NAME = 'taxCode';
    const JOIN_ALIAS = 'tc';

    /** @var string */
    protected $taxCodeClass = TaxCode::class;

    /** @var string */
    protected $relatedEntityClass;

    /** @var Expr */
    protected $expressionBuilder;

    /**
     * @param string $relatedEntityClass
     */
    public function __construct($relatedEntityClass)
    {
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
    protected function getColumnLabel()
    {
        return 'marello.tax.taxcode.entity_label';
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
            sprintf('IDENTITY(%s.taxCode) AS %s', $this->getAlias($config), $this->getDataName())
        );
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addJoin(DatagridConfiguration $config)
    {
        $config->getOrmQuery()->addLeftJoin(
            $this->getAlias($config).'.taxCode',
            $this->getJoinAlias()
        );
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addColumn(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(
            sprintf('[columns][%s]', $this->getDataName()),
            [
                'label' => $this->getColumnLabel(),
                'renderable' => false
            ]
        );
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addSorter(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(
            sprintf('[sorters][columns][%s]', $this->getDataName()),
            ['data_name' => $this->getDataName()]
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
                'type' => 'entity',
                'data_name' => $this->getAlias($config) . '.taxCode',
                'options' => [
                    'field_options' => [
                        'multiple' => true,
                        'class' => $this->taxCodeClass,
                        'choice_label' => 'code'
                    ]
                ],
                'enabled' => false
            ]
        );
    }
}
