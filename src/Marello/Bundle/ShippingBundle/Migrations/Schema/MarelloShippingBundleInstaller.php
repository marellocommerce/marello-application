<?php

namespace Marello\Bundle\ShippingBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloShippingBundleInstaller implements Installation, AttachmentExtensionAwareInterface
{
    /**
     * @var AttachmentExtension
     */
    protected $attachmentExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_3_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloShipmentTable($schema);
        $this->createMarelloShipMethodConfigTable($schema);
        $this->createMarelloShipMethodConfigsRuleTable($schema);
        $this->createMarelloShipMethodPostalCodeTable($schema);
        $this->createMarelloShipMethodTypeConfigTable($schema);
        $this->createMarelloShippingRuleDestinationTable($schema);
        $this->createMarelloTrackingInfoTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloShipmentForeignKeys($schema);
        $this->addMarelloShipMethodConfigForeignKeys($schema);
        $this->addMarelloShipMethodConfigsRuleForeignKeys($schema);
        $this->addMarelloShipMethodPostalCodeForeignKeys($schema);
        $this->addMarelloShipMethodTypeConfigForeignKeys($schema);
        $this->addMarelloShippingRuleDestinationForeignKeys($schema);
        $this->addMarelloTrackingInfoForeignKeys($schema);

        $this->attachmentExtension->addImageRelation(
            $schema,
            'marello_shipment', // entity table, e.g. oro_user, orocrm_contact etc.
            'shipping_label', // field name
            [], //additional options for relation
            20, // max allowed file size in megabytes, can be omitted, by default 1 Mb
            200, // thumbnail width in pixels, can be omitted, by default 32
            200 // thumbnail height in pixels, can be omitted, by default 32
        );
    }

    /**
     * Create marello_shipment table
     *
     * @param Schema $schema
     */
    protected function createMarelloShipmentTable(Schema $schema)
    {
        $table = $schema->createTable('marello_shipment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('shipping_service', 'string', ['length' => 255]);
        $table->addColumn('ups_shipment_digest', 'text', ['notnull' => false]);
        $table->addColumn('base64_encoded_label', 'text', ['notnull' => false]);
        $table->addColumn('identification_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('ups_package_tracking_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('tracking_info_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['tracking_info_id'], 'marello_shipment_trackinginfoidx');
    }

    /**
     * Create marello_ship_method_config table
     *
     * @param Schema $schema
     */
    protected function createMarelloShipMethodConfigTable(Schema $schema)
    {
        $table = $schema->createTable('marello_ship_method_config');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('rule_id', 'integer', ['notnull' => true]);
        $table->addColumn('method', 'string', ['length' => 255]);
        $table->addColumn('options', 'array', ['comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['rule_id'], 'idx_ba673c00744e0351');
    }

    /**
     * Create marello_ship_method_conf_rule table
     *
     * @param Schema $schema
     */
    protected function createMarelloShipMethodConfigsRuleTable(Schema $schema)
    {
        $table = $schema->createTable('marello_ship_method_conf_rule');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('rule_id', 'integer', ['notnull' => true]);
        $table->addColumn('currency', 'string', ['notnull' => true, 'length' => 3]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['rule_id'], 'idx_b068a5e0744e0351');
    }

    /**
     * Create marello_ship_method_post_code table
     *
     * @param Schema $schema
     */
    protected function createMarelloShipMethodPostalCodeTable(Schema $schema)
    {
        $table = $schema->createTable('marello_ship_method_post_code');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('destination_id', 'integer', []);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['destination_id'], 'idx_5cff199e816c6140');
    }

    /**
     * Create marello_ship_method_type_conf table
     *
     * @param Schema $schema
     */
    protected function createMarelloShipMethodTypeConfigTable(Schema $schema)
    {
        $table = $schema->createTable('marello_ship_method_type_conf');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('method_config_id', 'integer', []);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('options', 'array', ['comment' => '(DC2Type:array)']);
        $table->addColumn('enabled', 'boolean', ['default' => '0']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['method_config_id'], 'idx_214302ad3a3c93a5');
    }

    /**
     * Create marello_shipping_rule_dest table
     *
     * @param Schema $schema
     */
    protected function createMarelloShippingRuleDestinationTable(Schema $schema)
    {
        $table = $schema->createTable('marello_shipping_rule_dest');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('rule_id', 'integer', ['notnull' => true]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('country_code', 'string', ['length' => 2]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['region_code'], 'idx_778290c2aeb327af');
        $table->addIndex(['country_code'], 'idx_778290c2f026bb7c');
        $table->addIndex(['rule_id'], 'idx_778290c2744e0351');
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloTrackingInfoTable(Schema $schema)
    {
        $table = $schema->createTable('marello_tracking_info');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('tracking_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('track_trace_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('tracking_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('provider', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('provider_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('shipment_id', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['shipment_id'], 'marello_tracking_info_shipmentidx');
    }

    /**
     * Add marello_ship_method_config foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloShipMethodConfigForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_ship_method_config');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_ship_method_conf_rule'),
            ['rule_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_ship_method_conf_rule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloShipMethodConfigsRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_ship_method_conf_rule');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_rule'),
            ['rule_id'],
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
     * Add marello_ship_method_post_code foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloShipMethodPostalCodeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_ship_method_post_code');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_shipping_rule_dest'),
            ['destination_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_ship_method_type_conf foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloShipMethodTypeConfigForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_ship_method_type_conf');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_ship_method_config'),
            ['method_config_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_shipping_rule_dest foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloShippingRuleDestinationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_shipping_rule_dest');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_ship_method_conf_rule'),
            ['rule_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add marello_shipment foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloShipmentForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_shipment');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tracking_info'),
            ['tracking_info_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_tracking_info foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloTrackingInfoForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_tracking_info');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_shipment'),
            ['shipment_id'],
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
