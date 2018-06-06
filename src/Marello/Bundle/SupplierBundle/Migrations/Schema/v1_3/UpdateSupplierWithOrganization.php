<?php

namespace Marello\Bundle\SupplierBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloSupplierBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloSupplierSupplierTable($schema);
        $this->updateSupplierForeignKeys($schema);
    }

    /**
     * update marello_supplier_supplier table
     *
     * @param Schema $schema
     */
    protected function updateMarelloSupplierSupplierTable(Schema $schema)
    {
        $table = $schema->getTable('marello_supplier_supplier');
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['organization_id']);
    }

    /**
     * Update supplier table with foreign key on organization table
     * @param Schema $schema
     */
    protected function updateSupplierForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_supplier_supplier');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
