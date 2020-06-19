<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInventoryBundleDropSystemColumn implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 20;
    }


    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloInventoryWarehouseGroupTable($schema);
        $this->updateMarelloInventoryWarehouseChannelGroupLinkTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloInventoryWarehouseGroupTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_wh_group');

        $table->dropColumn('system');

    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloInventoryWarehouseChannelGroupLinkTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_wh_chg_link');

        $table->dropColumn('system');

    }
}
