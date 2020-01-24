<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOrderBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_order_customer');
        $table->addColumn('company_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_customer_company'),
            ['company_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
