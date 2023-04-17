<?php

namespace Marello\Bundle\NotificationAlertBundle\Tests\Unit\Datagrid;

use Marello\Bundle\NotificationAlertBundle\Datagrid\ActionPermissionProvider;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
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

    public function testGetNotificationAlertActionPermissionsResolveFalse(): void
    {
        $record = new ResultRecord(['resolved' => NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_YES]);
        $result = $this->provider->getNotificationAlertActionPermissions($record);
        $this->assertEquals(['resolve' => false, 'view' => true], $result);
    }

    public function testGetNotificationAlertActionPermissionsResolveTrue(): void
    {
        $record = new ResultRecord(['resolved' => NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NO]);
        $result = $this->provider->getNotificationAlertActionPermissions($record);
        $this->assertEquals(['resolve' => true, 'view' => true], $result);
    }
}
