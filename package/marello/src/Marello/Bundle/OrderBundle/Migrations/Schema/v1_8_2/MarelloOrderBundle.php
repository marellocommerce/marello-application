<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_8_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOrderBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // update table with organization id
        $table = $schema->getTable('marello_order_order_item');
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);

        // add index to organization column
        $table->addIndex(['organization_id']);

        // add foreign key constraint
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
