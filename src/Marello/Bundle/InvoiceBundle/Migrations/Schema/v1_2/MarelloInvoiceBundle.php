<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInvoiceBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateCustomerForeignKey($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function updateCustomerForeignKey(Schema $schema)
    {
        $table = $schema->getTable('marello_invoice_invoice');
        if ($table->hasForeignKey('FK_45AB65079395C3F3')) {
            $table->removeForeignKey('FK_45AB65079395C3F3');
        }
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_customer_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
