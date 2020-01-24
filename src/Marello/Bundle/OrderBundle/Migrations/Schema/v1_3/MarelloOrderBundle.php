<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloOrderBundle implements Migration
{
    const MARELLO_ORDER_TABLE = 'marello_order_order';
    const MARELLO_ORDER_ITEM_TABLE = 'marello_order_order_item';
    
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->modifyMarelloOrderOrderTableIndexes($schema);
        $this->modifyMarelloOrderOrderItemTableForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function modifyMarelloOrderOrderTableIndexes(Schema $schema)
    {
        $table = $schema->getTable(self::MARELLO_ORDER_TABLE);
        $table->addUniqueIndex(['shipment_id'], 'UNIQ_A619DD647BE036FC');

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_shipment'),
            ['shipment_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null],
            'FK_A619DD647BE036FC'
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function modifyMarelloOrderOrderItemTableForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::MARELLO_ORDER_ITEM_TABLE);
        if ($table->hasForeignKey('FK_1118665C66925E1D')) {
            $table->removeForeignKey('FK_1118665C66925E1D');
        }

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_code'),
            ['tax_code_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_1118665C66925E1D'
        );
    }
}
