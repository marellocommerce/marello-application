<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Schema\v3_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInvoiceBundleRemoveColumns implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 30;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateInvoiceTable($schema);
    }

    /**
     * @param Schema $schema
     */
    public function updateInvoiceTable(Schema $schema)
    {
        $table = $schema->getTable('marello_invoice_invoice');
        $table->dropColumn('payment_reference');
        $table->dropColumn('payment_details');
    }
}
