<?php

namespace Marello\Bundle\TaxBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
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
        $this->createMarelloTaxJurisdictionTable($schema);
        $this->createMarelloTaxZipCodeTable($schema);
        $this->updateMarelloTaxRuleTable($schema);
        $this->updateMarelloTaxRateTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloTaxJurisdictionForeignKeys($schema);
        $this->addMarelloTaxZipCodeForeignKeys($schema);
        $this->addMarelloTaxRuleForeignKeys($schema);
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
     * @param Schema $schema
     */
    protected function updateMarelloTaxRuleTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_rule');
        $table->addColumn('tax_jurisdiction_id', 'integer', ['notnull' => false]);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloTaxRateTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_rate');
        $table->changeColumn('rate', [
            'type' => $type = Type::getType('percent'),
            'notnull' => true,
            'comment' => '(DC2Type:percent)'
        ]);
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

    /**
     * Add oro_tax_rule foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloTaxRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_rule');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_jurisdiction'),
            ['tax_jurisdiction_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
