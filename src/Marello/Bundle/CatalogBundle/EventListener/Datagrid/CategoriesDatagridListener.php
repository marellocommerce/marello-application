<?php

namespace Marello\Bundle\CatalogBundle\EventListener\Datagrid;

use Doctrine\ORM\Query\Expr;
use Marello\Bundle\CatalogBundle\Provider\CategoriesChoicesProviderInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class CategoriesDatagridListener
{
    const DATA_NAME = 'categories';
    const JOIN_ALIAS = 'cat';

    /** @var CategoriesChoicesProviderInterface */
    protected $categoriesChoicesProvider;

    /** @var string */
    protected $relatedEntityClass;

    /** @var Expr */
    protected $expressionBuilder;

    /**
     * @param string $relatedEntityClass
     * @param CategoriesChoicesProviderInterface $categoriesChoicesProvider
     */
    public function __construct(
        $relatedEntityClass,
        CategoriesChoicesProviderInterface $categoriesChoicesProvider
    ) {
        $this->relatedEntityClass = $relatedEntityClass;
        $this->categoriesChoicesProvider = $categoriesChoicesProvider;

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
        return 'marello.catalog.category.entity_plural_label';
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
            sprintf('count(%s.code) AS categoriesCount', $this->getJoinAlias())
        );
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addJoin(DatagridConfiguration $config)
    {
        $config->getOrmQuery()->addLeftJoin(
            $this->getAlias($config).'.categories',
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
            'template' => 'MarelloCatalogBundle:Datagrid/Property:categories.html.twig',
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
            ['data_name' => 'categoriesCount']
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
                        'choices' => $this->categoriesChoicesProvider->getCategories()
                    ]
                ]
            ]
        );
    }
}
