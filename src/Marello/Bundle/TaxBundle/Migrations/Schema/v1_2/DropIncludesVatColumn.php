<?php

namespace Marello\Bundle\TaxBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DropIncludesVatColumn implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->dropIncludesVatColumn($schema);
    }
    
    /**
     * @param Schema $schema
     */
    protected function dropIncludesVatColumn(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_rule');
        if ($table->hasColumn('includes_vat')) {
            $table->dropColumn('includes_vat');
        }
    }
}
