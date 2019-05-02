<?php

namespace Marello\Bundle\SupplierBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloSupplierBundle implements
    Migration,
    AttachmentExtensionAwareInterface
{
    /** @var  AttachmentExtension */
    protected $attachmentExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloSupplierSupplierTable($schema);
        $this->createMarelloSupplierProductSupplierRelationTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloSupplierSupplierForeignKeys($schema);
        $this->addMarelloSupplierProductSupplierRelationForeignKeys($schema);
    }

    /**
     * Create marello_supplier_supplier table
     *
     * @param Schema $schema
     */
    protected function createMarelloSupplierSupplierTable(Schema $schema)
    {
        $table = $schema->createTable('marello_supplier_supplier');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('priority', 'integer', []);
        $table->addColumn('can_dropship', 'boolean', []);
        $table->addColumn('is_active', 'boolean', []);
        $table->addColumn('address_id', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['address_id'], '', []);
        $table->addUniqueIndex(['name']);

        $this->attachmentExtension->addAttachmentAssociation($schema, $table->getName());
    }

    /**
     * Create marello_supplier_prod_supp_rel table
     *
     * @param Schema $schema
     */
    protected function createMarelloSupplierProductSupplierRelationTable(Schema $schema)
    {
        $table = $schema->createTable('marello_supplier_prod_supp_rel');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => true]);
        $table->addColumn('supplier_id', 'integer', ['notnull' => true]);
        $table->addColumn('quantity_of_unit', 'integer', ['notnull' => true]);
        $table->addColumn('priority', 'integer', []);
        $table->addColumn(
            'cost',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('can_dropship', 'boolean', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(
            [
                'product_id',
                'supplier_id',
                'quantity_of_unit'
            ],
            'marello_supplier_prod_supp_rel_uidx'
        );
        $table->addIndex(['product_id'], '', []);
        $table->addIndex(['supplier_id'], '', []);
    }

    /**
     * Add marello_supplier_supplier foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSupplierSupplierForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_supplier_supplier');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['address_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_supplier_prod_supp_rel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSupplierProductSupplierRelationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_supplier_prod_supp_rel');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_supplier_supplier'),
            ['supplier_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Sets the AttachmentExtension
     *
     * @param AttachmentExtension $attachmentExtension
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }
}
