<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundleAddIsSystemColumn implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 10;
    }


    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloInventoryWarehouseGroupTable($schema, $queries);
        $this->updateMarelloInventoryWarehouseChannelGroupLinkTable($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function updateMarelloInventoryWarehouseGroupTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_inventory_wh_group');

        $table->addColumn('is_system', 'boolean', ['default' => false]);
        $query = "
            UPDATE marello_inventory_wh_group
                SET
                    is_system = system";
        $queries->addQuery($query);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function updateMarelloInventoryWarehouseChannelGroupLinkTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_inventory_wh_chg_link');

        $table->addColumn('is_system', 'boolean', ['default' => false]);
        $query = "
            UPDATE marello_inventory_wh_chg_link
                SET
                    is_system = system";
        $queries->addQuery($query);
    }
}
