<?php

namespace Marello\Bundle\NotificationBundle\Migrations\Schema\v1_2;

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
        $this->updateAttachmentForeignKeys($schema);
    }

    /**
     * Add foreign keys.
     *
     * @param Schema $schema
     */
    protected function updateAttachmentForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_notification_attach');
        $table->removeForeignKey('FK_70347799464E68B');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_attachment'),
            ['attachment_id'],
            ['id']
        );
    }
}
