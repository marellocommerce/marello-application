<?php

namespace Marello\Bundle\TaxBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloTaxBundleInstaller implements Installation
{

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_3_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloTaxTaxCodeTable($schema);
        $this->createMarelloTaxTaxRateTable($schema);
        $this->createMarelloTaxTaxRuleTable($schema);
        $this->createMarelloTaxJurisdictionTable($schema);
        $this->createMarelloTaxZipCodeTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloTaxJurisdictionForeignKeys($schema);
        $this->addMarelloTaxZipCodeForeignKeys($schema);
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
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
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
        $table->addColumn('rate', 'percent', ['notnull' => true, 'comment' => '(DC2Type:percent)']);
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
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
        $table->addColumn('tax_jurisdiction_id', 'integer', ['notnull' => false]);
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['tax_code_id'], '', []);
        $table->addIndex(['tax_rate_id'], '', []);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloTaxJurisdictionTable(Schema $schema)
    {
        $table = $schema->createTable('marello_tax_tax_jurisdiction');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'marello_tax_jurisdiction_codeidx');
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloTaxZipCodeTable(Schema $schema)
    {
        $table = $schema->createTable('marello_tax_zip_code');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('tax_jurisdiction_id', 'integer', []);
        $table->addColumn('zip_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('zip_range_start', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('zip_range_end', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
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
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_jurisdiction'),
            ['tax_jurisdiction_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloTaxJurisdictionForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_jurisdiction');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloTaxZipCodeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_zip_code');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_jurisdiction'),
            ['tax_jurisdiction_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
