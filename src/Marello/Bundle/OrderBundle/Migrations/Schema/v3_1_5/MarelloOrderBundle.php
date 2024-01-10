<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class MarelloOrderBundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $orderItemTable = $schema->getTable('marello_order_order_item');
        if (!$orderItemTable->hasColumn('item_type')) {
            $orderItemTable->addColumn('item_type', 'string', ['notnull' => false, 'length' => 255]);
        }

        $orderTable = $schema->getTable('marello_order_order');
        if ($orderTable->hasColumn('shipment_id')) {
            $orderTable->dropColumn('shipment_id');
        }
    }
}
