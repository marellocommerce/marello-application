<?php

namespace Marello\Bundle\CatalogBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloCatalogBundle implements Migration, ActivityExtensionAwareInterface
{
    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroCatalogCategoryTable($schema);
        $this->createOroCategoryToProductTable($schema);

        /** Foreign keys generation **/
        $this->addOroCatalogCategoryForeignKeys($schema);
        $this->addOroCategoryToProductForeignKeys($schema);
    }

    /**
     * Create oro_catalog_category table
     *
     * @param Schema $schema
     */
    protected function createOroCatalogCategoryTable(Schema $schema)
    {
        $table = $schema->createTable('marello_catalog_category');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code']);

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'marello_catalog_category');
    }

    /**
     * Create oro_category_to_product table
     *
     * @param Schema $schema
     */
    protected function createOroCategoryToProductTable(Schema $schema)
    {
        $table = $schema->createTable('marello_category_to_product');
        $table->addColumn('category_id', 'integer', []);
        $table->addColumn('product_id', 'integer', []);
        $table->setPrimaryKey(['category_id', 'product_id']);
    }

    /**
     * Add oro_catalog_category foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroCatalogCategoryForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_catalog_category');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_category_to_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroCategoryToProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_category_to_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_catalog_category'),
            ['category_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
