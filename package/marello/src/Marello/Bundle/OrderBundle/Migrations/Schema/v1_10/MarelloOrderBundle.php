<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOrderBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_order_order');
        $table->addColumn('payment_method_options', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
    }
}
