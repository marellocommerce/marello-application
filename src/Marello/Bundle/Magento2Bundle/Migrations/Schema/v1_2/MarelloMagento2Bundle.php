<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;

/**
 * @todo Remove this in final version
 */
class MarelloMagento2Bundle implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    private $extendExtension;

    /**
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createOrderTable($schema);
        $this->createCustomerTable($schema);
        $this->createMagentoAttributeSetTable($schema);

        $this->createOrderForeignKeys($schema);
        $this->createCustomerForeignKeys($schema);
        $this->addMagentoAttributeSetForeignKeys($schema);

        $this->addAttributeSetToAttributeFamilyRelation($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createOrderTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_order');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer');
        $table->addColumn('origin_id', 'integer', ['precision' => 0, 'unsigned' => true]);
        $table->addColumn('inner_order_id', 'integer');
        $table->addColumn('m2_customer_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['channel_id', 'origin_id'], 'unq_order_channel_idx');
    }

    /**
     * @param Schema $schema
     */
    protected function createCustomerTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_customer');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer');
        $table->addColumn('origin_id', 'integer', [
            'notnull' => false,
            'precision' => 0,
            'unsigned' => true
        ]);
        $table->addColumn('inner_customer_id', 'integer');
        $table->addColumn('hash_id', 'string', ['length' => 32]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['hash_id'], 'idx_m2_customer_hash_id');
        $table->addIndex(['origin_id'], 'idx_m2_customer_origin_id');
    }

    /**
     * @param Schema $schema
     */
    protected function createCustomerForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_customer_customer'),
            ['inner_customer_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * @param Schema $schema
     */
    protected function createMagentoAttributeSetTable(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_attributeset');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer');
        $table->addColumn('attribute_set_name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addIndex(['channel_id'], 'IDX_D427981972F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['channel_id', 'origin_id'], 'unq_attributeset_idx');
    }

    /**
     * @param Schema $schema
     */
    protected function createOrderForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_order');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order'),
            ['inner_order_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_m2_customer'),
            ['m2_customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMagentoAttributeSetForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_m2_attributeset');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addAttributeSetToAttributeFamilyRelation(Schema $schema)
    {
        $table = $schema->getTable('oro_attribute_family');
        $targetTable = $schema->getTable('marello_m2_attributeset');

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $targetTable,
            'attributeFamily',
            $table,
            'code',
            [
                ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true,
                    'on_delete' => 'SET NULL',
                ],
                'dataaudit' => ['auditable' => true]
            ]
        );

        $this->extendExtension->addManyToOneInverseRelation(
            $schema,
            $targetTable,
            'attributeFamily',
            $table,
            'magento2AttributeSet',
            ['attribute_set_name'],
            ['attribute_set_name'],
            ['attribute_set_name'],
            [
                ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'without_default' => true,
                    'on_delete' => 'SET NULL',
                ],
                'datagrid' => ['is_visible' => false],
                'form' => ['is_enabled' => false],
                'view' => ['is_displayable' => false],
                'merge' => ['display' => false],
                'importexport' => ['excluded' => true],
            ]
        );
    }
}
