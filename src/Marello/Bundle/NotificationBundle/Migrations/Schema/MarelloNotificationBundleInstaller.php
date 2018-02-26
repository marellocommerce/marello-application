<?php

namespace Marello\Bundle\NotificationBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloNotificationBundleInstaller implements Installation
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
        $this->createMarelloNotificationTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloNotificationForeignKeys($schema);
    }

    /**
     * Create marello_notification table
     *
     * @param Schema $schema
     */
    protected function createMarelloNotificationTable(Schema $schema)
    {
        $table = $schema->createTable('marello_notification');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('template_id', 'integer', []);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('recipients', 'json_array', []);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('body', 'text', []);
        $table->addIndex(['template_id'], 'idx_c883e8665da0fb8', []);
        $table->addIndex(['organization_id']);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add marello_notification foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloNotificationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_notification');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_email_template'),
            ['template_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
