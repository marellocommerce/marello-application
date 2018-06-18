<?php

namespace Marello\Bundle\TaxBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class MarelloTaxBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloTaxTaxCodeTable($schema);
        $this->createMarelloTaxTaxRateTable($schema);
        $this->createMarelloTaxTaxRuleTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloTaxTaxRuleForeignKeys($schema);
    }

    /**
     * Create marello_tax_tax_code table
     *
     * @param Schema $schema
     */
    protected function createMarelloTaxTaxCodeTable(Schema $schema)
    {
        $table = $schema->createTable('marello_tax_tax_code');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['notnull' => true, 'length' => 32]);
        $table->addColumn('description', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'marello_tax_code_codeidx');
    }

    /**
     * Create marello_tax_tax_rate table
     *
     * @param Schema $schema
     */
    protected function createMarelloTaxTaxRateTable(Schema $schema)
    {
        $table = $schema->createTable('marello_tax_tax_rate');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['notnull' => true, 'length' => 32]);
        $table->addColumn('rate', 'float', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'marello_tax_rate_codeidx');
    }

    /**
     * Create marello_tax_tax_rule table
     *
     * @param Schema $schema
     */
    protected function createMarelloTaxTaxRuleTable(Schema $schema)
    {
        $table = $schema->createTable('marello_tax_tax_rule');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('tax_code_id', 'integer', ['notnull' => false]);
        $table->addColumn('tax_rate_id', 'integer', ['notnull' => false]);
        $table->addColumn('includes_vat', 'boolean', []);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['tax_code_id'], '', []);
        $table->addIndex(['tax_rate_id'], '', []);
    }

    /**
     * Add marello_tax_tax_rule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloTaxTaxRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_rule');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_code'),
            ['tax_code_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_rate'),
            ['tax_rate_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
