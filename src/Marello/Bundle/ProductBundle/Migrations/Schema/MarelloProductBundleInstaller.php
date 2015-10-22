git <?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloProductBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloProductProductTable($schema);
        $this->createMarelloProductProductStatusTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloProductProductForeignKeys($schema);
    }

    /**
     * Create marello_product_product table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductProductTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_product');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('marello_product_status_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('stock_level', 'float', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cost', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['marello_product_status_name'], 'IDX_25845B8DA050B8C8', []);
        $table->addIndex(['user_owner_id'], 'IDX_25845B8D9EB185F9', []);
        $table->addIndex(['organization_id'], 'IDX_25845B8D32C8A3DE', []);
    }

    /**
     * Create marello_product_product_status table
     *
     * @param Schema $schema
     */
    protected function createMarelloProductProductStatusTable(Schema $schema)
    {
        $table = $schema->createTable('marello_product_product_status');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_DE31B8C7EA750E8');
    }

    /**
     * Add marello_product_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product_status'),
            ['marello_product_status_name'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
