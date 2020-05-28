<?php

namespace Marello\Bundle\TaxBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloTaxBundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->updateMarelloTaxCodeTable($schema);
    }

    /**
     * {@inheritDoc}
     */
    protected function updateMarelloTaxCodeTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tax_tax_code');
        $table->changeColumn('code', ['length' => 255]);
    }
}
