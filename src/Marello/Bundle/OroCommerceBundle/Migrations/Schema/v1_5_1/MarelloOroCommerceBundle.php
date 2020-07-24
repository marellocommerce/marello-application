<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Schema\v1_5_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

class MarelloOroCommerceBundle implements Migration
{
    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloCustomerTable($schema);
        $this->updateMarelloCompanyTable($schema);
        $this->updateMarelloOrderItemTable($schema);
    }

    /**
     * @param Schema $schema
     */
    public function updateMarelloCustomerTable(Schema $schema)
    {
        $table = $schema->getTable('marello_customer_customer');
        $table->addColumn(
            'orocommerce_origin_id',
            'integer',
            [
                'notnull' => false,
                'oro_options' => [
                    'extend' => [
                        'is_extend' => true,
                        'owner' => ExtendScope::OWNER_CUSTOM
                    ],
                    'entity' => ['label' => 'Origin ID'],
                    'datagrid' => ['is_visible' => false],
                    'form' => ['is_enabled' => false],
                    'view' => ['is_displayable' => false]
                ]
            ]
        );
    }

    /**
     * @param Schema $schema
     */
    public function updateMarelloCompanyTable(Schema $schema)
    {
        $table = $schema->getTable('marello_customer_company');
        $table->addColumn(
            'orocommerce_origin_id',
            'integer',
            [
                'notnull' => false,
                'oro_options' => [
                    'extend' => [
                        'is_extend' => true,
                        'owner' => ExtendScope::OWNER_CUSTOM
                    ],
                    'entity' => ['label' => 'Origin ID'],
                    'datagrid' => ['is_visible' => false],
                    'form' => ['is_enabled' => false],
                    'view' => ['is_displayable' => false]
                ]
            ]
        );
    }


    /**
     * @param Schema $schema
     */
    public function updateMarelloOrderItemTable(Schema $schema)
    {
        $table = $schema->getTable('marello_order_order_item');
        $table->addColumn(
            'orocommerce_origin_id',
            'integer',
            [
                'notnull' => false,
                'oro_options' => [
                    'extend' => [
                        'is_extend' => true,
                        'owner' => ExtendScope::OWNER_CUSTOM
                    ],
                    'entity' => ['label' => 'Origin ID'],
                    'datagrid' => ['is_visible' => false],
                    'form' => ['is_enabled' => false],
                    'view' => ['is_displayable' => false]
                ]
            ]
        );
    }
}
