<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOrderBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_order_order');
        $table->dropColumn('locale');
        $table->addColumn('payment_method_options', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);

        $dropLocaleInConfigSql = <<<EOF
DELETE FROM oro_entity_config_field
WHERE field_name = :field_name
AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class_name)
EOF;
        $dropLocaleInConfigQuery = new ParametrizedSqlMigrationQuery();
        $dropLocaleInConfigQuery->addSql(
            $dropLocaleInConfigSql,
            ['field_name' => 'locale', 'class_name' => Order::class],
            ['field_name' => Type::STRING, 'class_name' => Type::STRING]
        );
        $queries->addPostQuery($dropLocaleInConfigQuery);
    }
}
