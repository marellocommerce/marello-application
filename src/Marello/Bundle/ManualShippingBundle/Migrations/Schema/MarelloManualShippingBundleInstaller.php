<?php

namespace Marello\Bundle\ManualShippingBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloManualShippingBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloManualShippingTransportLabelTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloManualShippingTransportLabelForeignKeys($schema);
    }

    /**
     * Create marello_manual_shipping_transport_label table
     *
     * @param Schema $schema
     */
    protected function createMarelloManualShippingTransportLabelTable(Schema $schema)
    {
        if (!$schema->hasTable('marello_man_ship_transp_label')) {
            $table = $schema->createTable('marello_man_ship_transp_label');
            $table->addColumn('transport_id', 'integer', []);
            $table->addColumn('localized_value_id', 'integer', []);
            $table->addUniqueIndex(['localized_value_id'], 'marello_manual_ship_transport_label_localized_value_id');
            $table->setPrimaryKey(['transport_id', 'localized_value_id']);
            $table->addIndex(['transport_id'], 'marello_manual_ship_transport_label_transport_id', []);
        }
    }

    /**
     * Add marello_manual_shipping_transport_label foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloManualShippingTransportLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_man_ship_transp_label');
        if (!$table->hasForeignKey('transport_id')) {
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_integration_transport'),
                ['transport_id'],
                ['id'],
                ['onUpdate' => null, 'onDelete' => 'CASCADE']
            );
        }

        if (!$table->hasForeignKey('localized_value_id')) {
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_fallback_localization_val'),
                ['localized_value_id'],
                ['id'],
                ['onUpdate' => null, 'onDelete' => 'CASCADE']
            );
        }
    }
}
