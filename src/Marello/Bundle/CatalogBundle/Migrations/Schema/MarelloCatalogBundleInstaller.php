<?php

namespace Marello\Bundle\CatalogBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloCatalogBundleInstaller implements Installation, ActivityExtensionAwareInterface
{
    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_1_1';
    }

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
        $this->createCatalogCategoryTable($schema);
        $this->createCategoryToProductTable($schema);

        /** Foreign keys generation **/
        $this->addCatalogCategoryForeignKeys($schema);
        $this->addCategoryToProductForeignKeys($schema);
    }

    /**
     * Create marello_catalog_category table
     *
     * @param Schema $schema
     */
    protected function createCatalogCategoryTable(Schema $schema)
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
        $table->addUniqueIndex(['code'], 'marello_catalog_category_codeidx');

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'marello_catalog_category');
    }

    /**
     * Create marello_category_to_product table
     *
     * @param Schema $schema
     */
    protected function createCategoryToProductTable(Schema $schema)
    {
        $table = $schema->createTable('marello_category_to_product');
        $table->addColumn('category_id', 'integer', []);
        $table->addColumn('product_id', 'integer', []);
        $table->setPrimaryKey(['category_id', 'product_id']);
    }

    /**
     * Add marello_catalog_category foreign keys.
     *
     * @param Schema $schema
     */
    protected function addCatalogCategoryForeignKeys(Schema $schema)
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
     * Add marello_category_to_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addCategoryToProductForeignKeys(Schema $schema)
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
