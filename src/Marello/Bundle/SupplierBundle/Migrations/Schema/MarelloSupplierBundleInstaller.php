<?php

namespace Marello\Bundle\SupplierBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloSupplierBundleInstaller implements
    Installation,
    AttachmentExtensionAwareInterface
{
    /** @var  AttachmentExtension */
    protected $attachmentExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_4';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloSupplierSupplierTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloSupplierSupplierForeignKeys($schema);
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
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('priority', 'integer', []);
        $table->addColumn('can_dropship', 'boolean', []);
        $table->addColumn('is_active', 'boolean', []);
        $table->addColumn('address_id', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('currency', 'string', ['length' => 3]);
        $table->addColumn('po_send_by', 'string', ['length' => 30]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id']);
        $table->addUniqueIndex(['address_id'], 'UNIQ_16532C7BF5B7AF75', []);
        $table->addUniqueIndex(['name']);
        $table->addUniqueIndex(['email']);

        $this->attachmentExtension->addAttachmentAssociation($schema, $table->getName());
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

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
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
