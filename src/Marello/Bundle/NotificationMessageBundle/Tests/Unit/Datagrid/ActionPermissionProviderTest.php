<?php

namespace Marello\Bundle\NotificationMessageBundle\Tests\Unit\Datagrid;

use Marello\Bundle\NotificationMessageBundle\Datagrid\ActionPermissionProvider;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use PHPUnit\Framework\TestCase;

class ActionPermissionProviderTest extends TestCase
{
    /** @var ActionPermissionProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new ActionPermissionProvider();
    }

    public function testGetNotificationMessageActionPermissionsResolveFalse(): void
    {
        $record = new ResultRecord(['resolved' => NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_YES]);
        $result = $this->provider->getNotificationMessageActionPermissions($record);
        $this->assertEquals(['resolve' => false, 'view' => true], $result);
    }

    public function testGetNotificationMessageActionPermissionsResolveTrue(): void
    {
        $record = new ResultRecord(['resolved' => NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NO]);
        $result = $this->provider->getNotificationMessageActionPermissions($record);
        $this->assertEquals(['resolve' => true, 'view' => true], $result);
    }
}
