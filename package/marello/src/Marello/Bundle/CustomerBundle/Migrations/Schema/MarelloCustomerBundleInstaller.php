<?php

namespace Marello\Bundle\CustomerBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloCustomerBundleInstaller implements Installation
{
    const MARELLO_COMPANY_TABLE = 'marello_customer_company';
    const MARELLO_COMPANY_JOIN_ADDRESS_TABLE = 'marello_company_join_address';

    /**
     * @inheritDoc
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloCompanyTable($schema);
        $this->createMarelloCompanyJoinAddressTable($schema);
        $this->addMarelloCompanyForeignKeys($schema);
        $this->addMarelloCompanyJoinAddressForeignKeys($schema);
    }

    /**
     * Create marello_customer_company table
     *
     * @param Schema $schema
     */
    protected function createMarelloCompanyTable(Schema $schema)
    {
        $table = $schema->createTable(self::MARELLO_COMPANY_TABLE);

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloCompanyJoinAddressTable(Schema $schema)
    {
        $table = $schema->createTable(self::MARELLO_COMPANY_JOIN_ADDRESS_TABLE);
        $table->addColumn('company_id', 'integer', ['notnull' => true]);
        $table->addColumn('address_id', 'integer', ['notnull' => true]);
        $table->addUniqueIndex(['address_id'], 'UNIQ_629E2BBEA750E85234');
        $table->setPrimaryKey(['company_id', 'address_id']);
    }

    /**
     * Add oro_customer foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloCompanyForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(static::MARELLO_COMPANY_TABLE);
        $table->addForeignKeyConstraint(
            $table,
            ['parent_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloCompanyJoinAddressForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::MARELLO_COMPANY_JOIN_ADDRESS_TABLE);
        $table->addForeignKeyConstraint(
            $schema->getTable(self::MARELLO_COMPANY_TABLE),
            ['company_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
