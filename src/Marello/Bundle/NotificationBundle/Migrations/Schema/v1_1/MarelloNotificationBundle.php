<?php

namespace Marello\Bundle\NotificationBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloNotificationBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createAttachmentsTable($schema);
        $this->addAttachmentForeignKeys($schema);
    }

    /**
     * Create marello_notification_attachment table
     *
     * @param Schema $schema
     */
    protected function createAttachmentsTable(Schema $schema)
    {
        $table = $schema->createTable('marello_notification_attach');
        $table->addColumn('notification_id', 'integer');
        $table->addColumn('attachment_id', 'integer');
        $table->setPrimaryKey(['notification_id', 'attachment_id']);
    }

    /**
     * Add foreign keys.
     *
     * @param Schema $schema
     */
    protected function addAttachmentForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_notification_attach');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_notification'),
            ['notification_id'],
            ['id']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_attachment_file'),
            ['attachment_id'],
            ['id']
        );
    }
}
