<?php

namespace Marello\Bundle\NotificationAlertBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertSourceInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertTypeInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloNotificationAlertBundleInstaller implements Installation, ActivityExtensionAwareInterface, ExtendExtensionAwareInterface
{
    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloNotificationAlertTable($schema);
        $this->addMarelloNotificationAlertForeignKeys($schema);
    }

    protected function createMarelloNotificationAlertTable(Schema $schema)
    {
        $table = $schema->createTable('marello_notification_alert');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('message', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('related_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_item_class', 'string', ['notnull' => false, 'length' => 100]);
        $table->addColumn('solution', 'text', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_group_id', 'integer', ['notnull' => false]);
        $table->addColumn('count', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)', 'notnull' => false]);
        $table->addIndex(['organization_id']);
        $table->setPrimaryKey(['id']);

        $name = $table->getName();
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_task', $name);
        $this->activityExtension->addActivityAssociation($schema, $name, 'marello_order_order');
        $this->activityExtension->addActivityAssociation($schema, $name, 'marello_purchase_order');
        $this->activityExtension->addActivityAssociation($schema, $name, 'marello_inventory_allocation');

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'resolved',
            NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_ENUM_CODE,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'alertType',
            NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_ENUM_CODE,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'source',
            NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_ENUM_CODE,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
    }

    protected function addMarelloNotificationAlertForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_notification_alert');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_access_group'),
            ['user_group_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
