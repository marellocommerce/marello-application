<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOrderBundle implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOrderTable($schema);
        $this->updateOrderItemTable($schema);
    }

    private function updateOrderTable(Schema $schema)
    {
        $table = $schema->getTable('marello_order_order');
        if (!$table->hasColumn('delivery_date')) {
            $table->addColumn('delivery_date', 'datetime', ['notnull' => false]);
        }
        if (!$table->hasColumn('order_note')) {
            $table->addColumn('order_note', 'text', ['notnull' => false]);
        }
        if (!$table->hasColumn('po_number')) {
            $table->addColumn('po_number', 'string', ['length' => 255, 'notnull' => false]);
        }
    }

    private function updateOrderItemTable(Schema $schema)
    {
        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('marello_product_unit');
        // enum table is already available and created...
        if ($schema->hasTable($tableName)) {
            return;
        }

        $table = $schema->getTable('marello_order_order_item');
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'productUnit',
            'marello_product_unit',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
    }

    /**
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
