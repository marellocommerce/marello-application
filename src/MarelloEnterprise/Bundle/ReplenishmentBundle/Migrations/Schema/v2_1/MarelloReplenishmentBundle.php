<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\NameGeneratorAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Tools\DbIdentifierNameGenerator;

class MarelloReplenishmentBundle implements Migration, NameGeneratorAwareInterface
{
    /**
     * @var DbIdentifierNameGenerator
     */
    protected $nameGenerator;

    public function setNameGenerator(DbIdentifierNameGenerator $nameGenerator)
    {
        $this->nameGenerator = $nameGenerator;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloReplenishmentOrderManualItemTable($schema);
        $this->addMarelloReplenishmentOrderManualItemConfigForeignKeys($schema);
        $this->updateMarelloReplenishmentConfigTable($schema);
        $this->updateMarelloReplenishmentItemTable($schema);
        $this->updateMarelloReplenishmentOrderTable($schema);
    }

    protected function createMarelloReplenishmentOrderManualItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_repl_order_m_item_config');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false]);
        $table->addColumn('destination_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_id', 'integer', ['notnull' => false]);
        $table->addColumn('quantity', 'integer', ['notnull' => false]);
        $table->addColumn('all_quantity', 'boolean', []);
        $table->addColumn('available_quantity', 'integer', ['notnull' => false]);
        $table->addColumn('order_config_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    protected function addMarelloReplenishmentOrderManualItemConfigForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_repl_order_m_item_config');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['origin_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['destination_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_repl_order_config'),
            ['order_config_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    protected function updateMarelloReplenishmentItemTable(Schema $schema)
    {
        $table = $schema->getTable('marello_repl_order_item');
        $table->addColumn('all_quantity', 'boolean', ['notnull' => false]);
        $table->changeColumn('product_id', ['notnull' => false]);

        $indexName = $this->nameGenerator->generateForeignKeyConstraintName(
            'marello_repl_order_item',
            ['product_id']
        );
        $table->removeForeignKey($indexName);

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    protected function updateMarelloReplenishmentConfigTable(Schema $schema)
    {
        $table = $schema->getTable('marello_repl_order_config');
        $table->changeColumn('origins', ['notnull' => false]);
        $table->changeColumn('destinations', ['notnull' => false]);
        $table->changeColumn('products', ['notnull' => false]);
        $table->changeColumn('percentage', ['notnull' => false]);
    }

    protected function updateMarelloReplenishmentOrderTable(Schema $schema)
    {
        $table = $schema->getTable('marello_repl_order');
        $table->changeColumn('origin_id', ['notnull' => false]);
        $table->changeColumn('destination_id', ['notnull' => false]);
        $table->changeColumn('repl_order_config_id', ['notnull' => false]);
        $table->dropColumn('percentage');

        $indexName = $this->nameGenerator->generateForeignKeyConstraintName(
            'marello_repl_order',
            ['origin_id']
        );
        $table->removeForeignKey($indexName);
        $indexName = $this->nameGenerator->generateForeignKeyConstraintName(
            'marello_repl_order',
            ['destination_id']
        );
        $table->removeForeignKey($indexName);
        $indexName = $this->nameGenerator->generateForeignKeyConstraintName(
            'marello_repl_order',
            ['repl_order_config_id']
        );
        $table->removeForeignKey($indexName);

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['origin_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['destination_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_repl_order_config'),
            ['repl_order_config_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
 }
