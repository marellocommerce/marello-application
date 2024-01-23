<?php

namespace Marello\Bundle\WebhookBundle\Tests\Unit\Entity;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class WebhookTest extends TestCase
{
    use EntityTestCaseTrait;
    use EntityTrait;

    /**
     * @var Webhook
     */
    protected $target;

    protected function setUp(): void
    {
        $this->target = new Webhook();
    }

    public function testProperties()
    {
        $properties = [
            ['id', 123, false],
            ['name', 'inventory-update', ''],
            ['event', 'inventory_update', ''],
            ['secret', '012365xyz', ''],
            ['callbackUrl', 'https://domain.com/xy/', ''],
            ['organization', new Organization()],
            ['localization', new Localization()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()]
        ];
        static::assertPropertyAccessors($this->target, $properties);
    }
}
