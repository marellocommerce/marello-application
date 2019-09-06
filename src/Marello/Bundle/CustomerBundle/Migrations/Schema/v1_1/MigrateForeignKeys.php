<?php

namespace Marello\Bundle\CustomerBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateForeignKeys implements
    Migration,
    OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                'INSERT INTO marello_customer_customer (
                    id,
                    organization_id,
                    primary_address_id, 
                    shipping_address_id,
                    company_id,
                    created_at,
                    updated_at,
                    name_prefix,
                    first_name,
                    middle_name,
                    last_name,
                    name_suffix,
                    email,
                    tax_identification_number,
                    serialized_data
                )
                SELECT
                    id,
                    organization_id,
                    primary_address_id, 
                    shipping_address_id,
                    company_id,
                    created_at,
                    updated_at,
                    name_prefix,
                    first_name,
                    middle_name,
                    last_name,
                    name_suffix,
                    email,
                    tax_identification_number,
                    serialized_data
                FROM marello_order_customer'
            )
        );
        $orderTable = $schema->getTable('marello_order_order');
        $orderTable->addForeignKeyConstraint(
            'marello_customer_customer',
            ['customer_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );

        $addressTable = $schema->getTable('marello_address');
        $addressTable->addForeignKeyConstraint(
            'marello_customer_customer',
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        $emailTable = $schema->getTable('oro_email_address');
        $emailTable->addForeignKeyConstraint(
            'marello_customer_customer',
            ['owner_marello_customer_id'],
            ['id']
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }
}
