<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        $table->dropColumn('locale');

        $dropLocaleInConfigSql = <<<EOF
DELETE FROM oro_entity_config_field
WHERE field_name = :field_name
AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class_name)
EOF;
        $dropLocaleInConfigQuery = new ParametrizedSqlMigrationQuery();
        $dropLocaleInConfigQuery->addSql(
            $dropLocaleInConfigSql,
            ['field_name' => 'locale', 'class_name' => SalesChannel::class],
            ['field_name' => Type::STRING, 'class_name' => Type::STRING]
        );
        $queries->addPostQuery($dropLocaleInConfigQuery);
    }
}
