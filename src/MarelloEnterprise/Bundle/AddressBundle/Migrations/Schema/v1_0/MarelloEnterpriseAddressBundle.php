<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class MarelloEnterpriseAddressBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloEnterpriseAddressTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloEnterpriseAddressForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloEnterpriseAddressTable(Schema $schema)
    {
        $table = $schema->createTable('marelloenterprise_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('address_id', 'integer', ['notnull' => false]);
        $table->addColumn('latitude', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('longitude', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['address_id'], 'UNIQ_1C8377619395C3F30', []);
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloEnterpriseAddressForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marelloenterprise_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['address_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
