<?php

namespace Marello\Bundle\AddressBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class MarelloAddressBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->updateDatetimeMarelloAddressTable($schema, $queries);
    }

    /**
     * Update marello_address table
     *
     * @param Schema $schema
     */
    protected function updateDatetimeMarelloAddressTable(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery('UPDATE marello_address SET updated=NOW()');
        $table = $schema->getTable('marello_address');
        $table->getColumn('updated')->setNotnull(true);
    }
}
