<?php

namespace Marello\Bundle\ReturnBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloReturnBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_return_return');
        $table->dropColumn('locale');

        $dropLocaleInConfigSql = <<<EOF
DELETE FROM oro_entity_config_field
WHERE field_name = :field_name
AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class_name)
EOF;
        $dropLocaleInConfigQuery = new ParametrizedSqlMigrationQuery();
        $dropLocaleInConfigQuery->addSql(
            $dropLocaleInConfigSql,
            ['field_name' => 'locale', 'class_name' => ReturnEntity::class],
            ['field_name' => Type::STRING, 'class_name' => Type::STRING]
        );
        $queries->addPostQuery($dropLocaleInConfigQuery);
    }
}
