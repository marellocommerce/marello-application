<?php

namespace Marello\Bundle\ShippingBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloShippingBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->updateMarelloShipmentTable($schema);
        
        $this->addMarelloShipmentForeignKeys($schema);
    }

    /**
     * Create marello_shipment table
     *
     * @param Schema $schema
     */
    protected function updateMarelloShipmentTable(Schema $schema)
    {
        $table = $schema->getTable('marello_shipment');
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
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
    }
}
