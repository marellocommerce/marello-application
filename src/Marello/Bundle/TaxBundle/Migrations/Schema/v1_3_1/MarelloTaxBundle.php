<?php

namespace Marello\Bundle\TaxBundle\Migrations\Schema\v1_3_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

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
        $this->updateMarelloTaxRateTable($schema);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     */
    protected function updateMarelloTaxCodeTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_code');
        if ($table->hasIndex('uniq_marello_tax_tax_code_code')) {
            $table->renameIndex('uniq_marello_tax_tax_code_code', 'marello_tax_code_codeidx');
        }
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     */
    protected function updateMarelloTaxJurisdictionTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_jurisdiction');
        if ($table->hasIndex('UNIQ_2CBEF9AE77153098')) {
            $table->renameIndex('UNIQ_2CBEF9AE77153098', 'marello_tax_jurisdiction_codeidx');
        }
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     */
    protected function updateMarelloTaxRateTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_rate');
        if ($table->hasIndex('uniq_marello_tax_tax_rate_code')) {
            $table->renameIndex('uniq_marello_tax_tax_rate_code', 'marello_tax_rate_codeidx');
        }
    }
}
