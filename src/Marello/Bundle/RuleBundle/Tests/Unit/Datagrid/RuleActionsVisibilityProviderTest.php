<?php

namespace Marello\Bundle\RuleBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Marello\Bundle\RuleBundle\Datagrid\RuleActionsVisibilityProvider;
use Marello\Bundle\RuleBundle\Entity\Rule;

class RuleActionsVisibilityProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RuleActionsVisibilityProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new RuleActionsVisibilityProvider();
    }

    /**
     * @param bool  $enabled
     * @param array $actions
     * @param array $expected
     * @param bool  $isSystem
     *
     * @dataProvider recordsDataProvider
     */
    public function testGetActionsVisibility($enabled, array $actions, array $expected, $isSystem)
    {
        $rule = $this->createMock(Rule::class);
        $rule->expects(static::any())
            ->method('isEnabled')
            ->willReturn($enabled);
        $rule->expects(static::any())
            ->method('isSystem')
            ->willReturn($isSystem);
        $this->assertEquals(
            $expected,
            $this->provider->getActionsVisibility(new ResultRecord(['rule' => $rule]), $actions)
        );
    }

    /**
     * @return array
     */
    public function recordsDataProvider()
    {
        return [
            'enabledNotSystem' => [
                true,
                ['enable' => ['config'], 'disable' => ['config'], 'delete' => ['config']],
                ['enable' => false, 'disable' => true, 'delete' => true],
                false
            ],
            'disabledNotSystem' => [
                false,
                ['enable' => ['config'], 'disable' => ['config'], 'delete' => ['config']],
                ['enable' => true, 'disable' => false, 'delete' => true],
                false
            ],
            'enabledSystem' => [
                true,
                ['enable' => ['config'], 'disable' => ['config'], 'delete' => ['config']],
                ['enable' => false, 'disable' => false, 'update' => false, 'delete' => false],
                true
            ],
            'disabledSystem' => [
                false,
                ['enable' => ['config'], 'disable' => ['config'], 'delete' => ['config']],
                ['enable' => false, 'disable' => false, 'update' => false, 'delete' => false],
                true
            ],
        ];
    }
}
