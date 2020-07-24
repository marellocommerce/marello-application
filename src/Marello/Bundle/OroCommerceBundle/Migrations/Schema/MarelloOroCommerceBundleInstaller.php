<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

class MarelloOroCommerceBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_5_1';
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOroIntegrationTransportTable($schema);
        $this->updateMarelloCustomerTable($schema);
        $this->updateMarelloCompanyTable($schema);
        $this->updateMarelloOrderItemTable($schema);
    }

    /**
     * @param Schema $schema
     */
    public function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');

        $table->addColumn('orocommerce_url', 'string', ['notnull' => false, 'length' => 1024]);
        $table->addColumn('orocommerce_currency', 'string', ['notnull' => false, 'length' => 3]);
        $table->addColumn('orocommerce_username', 'string', ['notnull' => false, 'length' => 1024]);
        $table->addColumn('orocommerce_key', 'string', ['notnull' => false, 'length' => 1024]);
        $table->addColumn('orocommerce_productunit', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('orocommerce_customertaxcode', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_pricelist', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_productfamily', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_enterprise', 'boolean', ['notnull' => false]);
        $table->addColumn('orocommerce_warehouse', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_businessunit', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_deldataondeactiv', 'boolean', ['notnull' => false]);
        $table->addColumn('orocommerce_deldataondel', 'boolean', ['notnull' => false]);
        $table->addColumn('orocommerce_data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('orocommerce_scg_id', 'integer', ['notnull' => false]);
        $table->addIndex(['orocommerce_scg_id'], null, []);

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_channel_group'),
            ['orocommerce_scg_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
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
