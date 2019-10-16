<?php

namespace Marello\Bundle\CustomerBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateTable implements
    Migration,
    OrderedMigrationInterface,
    ActivityExtensionAwareInterface,
    AttachmentExtensionAwareInterface
{
    const MARELLO_CUSTOMER_TABLE = 'marello_customer_customer';

    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * @var AttachmentExtension
     */
    protected $attachmentExtension;
    
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery(
            new UpdateEntityConfigExtendClassQuery()
        );
        if (!$schema->hasTable(self::MARELLO_CUSTOMER_TABLE)) {
            $this->createMarelloCustomerTable($schema);
            $this->createActivityListToCustomerRelTable($schema);
            $this->addMarelloCustomerForeignKeys($schema);
            $this->addActivityListToCustomerRelTableForeignKeys($schema);
        }
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloCustomerTable(Schema $schema)
    {
        $table = $schema->createTable(self::MARELLO_CUSTOMER_TABLE);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('primary_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('email', 'text', []);
        $table->addColumn('tax_identification_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('company_id', 'integer', ['notnull' => false]);
        $table->addColumn('serialized_data', 'text', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['primary_address_id'], 'UNIQ_75C456C9F5B7AF75');
        $table->addUniqueIndex(['shipping_address_id'], 'UNIQ_75C456C94D4CFF2B');
        $table->addIndex(['organization_id']);

        $this->attachmentExtension->addAttachmentAssociation($schema, $table->getName());
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', $table->getName());
    }

    /**
     * @param Schema $schema
     */
    protected function createActivityListToCustomerRelTable(Schema $schema)
    {
        $table = $schema->createTable('oro_rel_c3990ba6784fec5f7f9667');
        $table->addColumn('activitylist_id', 'integer', ['notnull' => true]);
        $table->addColumn('customer_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['activitylist_id', 'customer_id']);
    }

    /**
     * @param Schema $schema
     */
    protected function addActivityListToCustomerRelTableForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_rel_c3990ba6784fec5f7f9667');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_activity_list'),
            ['activitylist_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_customer_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloCustomerForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::MARELLO_CUSTOMER_TABLE);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['primary_address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['shipping_address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_customer_company'),
            ['company_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Sets the ActivityExtension
     *
     * @param ActivityExtension $activityExtension
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
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
