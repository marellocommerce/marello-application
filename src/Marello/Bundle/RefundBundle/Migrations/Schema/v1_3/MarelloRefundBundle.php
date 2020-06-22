<?php

namespace Marello\Bundle\RefundBundle\Migrations\Schema\v1_3;

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
        $table->dropColumn('locale');
        if ($table->hasForeignKey('fk_marello_refund_customer_id')) {
            $table->removeForeignKey('fk_marello_refund_customer_id');
        }
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_customer_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );

        $dropLocaleInConfigSql = <<<EOF
DELETE FROM oro_entity_config_field
WHERE field_name = :field_name
AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class_name)
EOF;
        $dropLocaleInConfigQuery = new ParametrizedSqlMigrationQuery();
        $dropLocaleInConfigQuery->addSql(
            $dropLocaleInConfigSql,
            ['field_name' => 'locale', 'class_name' => Refund::class],
            ['field_name' => Type::STRING, 'class_name' => Type::STRING]
        );
        $queries->addPostQuery($dropLocaleInConfigQuery);
    }
}
