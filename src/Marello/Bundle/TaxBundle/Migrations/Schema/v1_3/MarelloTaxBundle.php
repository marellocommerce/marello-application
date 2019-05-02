<?php

namespace Marello\Bundle\TaxBundle\Migrations\Schema\v1_3;

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
        $this->updateMarelloTaxCodeTable($schema);
        $this->updateMarelloTaxJurisdictionTable($schema);
        $this->updateMarelloTaxRuleTable($schema);
        $this->updateMarelloTaxRateTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloTaxCodeTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_code');
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
    }
    
    /**
     * @param Schema $schema
     */
    protected function updateMarelloTaxJurisdictionTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_jurisdiction');
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
    }
    
    /**
     * @param Schema $schema
     */
    protected function updateMarelloTaxRuleTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_rule');
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloTaxRateTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_rate');
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
    }
}
