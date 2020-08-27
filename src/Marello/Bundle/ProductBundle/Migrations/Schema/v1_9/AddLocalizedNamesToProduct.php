<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddLocalizedNamesToProduct implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloProductProductNameTable($schema);
        $this->addMarelloProductNameForeignKeys($schema);

        $dropFields = ['name', 'cost'];
        $dropFieldInConfigSql = <<<EOF
DELETE FROM oro_entity_config_field
WHERE field_name = :field_name
AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class_name)
EOF;
        foreach ($dropFields as $field) {
            $dropFieldInConfigQuery = new ParametrizedSqlMigrationQuery();
            $dropFieldInConfigQuery->addSql(
                $dropFieldInConfigSql,
                ['field_name' => $field, 'class_name' => Product::class],
                ['field_name' => Type::STRING, 'class_name' => Type::STRING]
            );
            $queries->addPostQuery($dropFieldInConfigQuery);
        }
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloProductProductNameTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_product_name');
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['product_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], 'uniq_58b39126eb576e89');
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloProductNameForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_product_name');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
