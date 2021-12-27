<?php

namespace Marello\Bundle\RefundBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloRefundBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_refund');
        if (!$table->hasColumn('refund_subtotal')) {
            $table->addColumn('refund_subtotal', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        }

        if (!$table->hasColumn('refund_tax_total')) {
            $table->addColumn('refund_tax_total', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        }

        $table = $schema->getTable('marello_refund_item');
        if (!$table->hasColumn('subtotal')) {
            $table->addColumn('subtotal', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        }

        if (!$table->hasColumn('tax_total')) {
            $table->addColumn('tax_total', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        }

        if (!$table->hasColumn('tax_code_id')) {
            $table->addColumn('tax_code_id', 'integer', ['notnull' => false]);
        }
    }
}
