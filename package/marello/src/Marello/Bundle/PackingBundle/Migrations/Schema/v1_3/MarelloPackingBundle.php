<?php

namespace Marello\Bundle\PackingBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloPackingBundle implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloPackingSlipTable($schema);
        $this->updateMarelloPackingSlipItemTable($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateMarelloPackingSlipTable(Schema $schema)
    {
        $table = $schema->getTable('marello_packing_packing_slip');
        if ($table->hasForeignKey('FK_B0E654D9395C3F3')) {
            $table->removeForeignKey('FK_B0E654D9395C3F3');
        }
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_customer_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateMarelloPackingSlipItemTable(Schema $schema)
    {
        $table = $schema->getTable('marello_packing_pack_slip_item');
        $table->addColumn('inventory_batches', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }
}
