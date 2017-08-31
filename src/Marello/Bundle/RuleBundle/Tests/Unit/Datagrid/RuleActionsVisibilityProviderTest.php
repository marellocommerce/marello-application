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
     *
     * @dataProvider recordsDataProvider
     */
    public function testGetActionsVisibility($enabled, array $actions, array $expected)
    {
        $rule = $this->createMock(Rule::class);
        $rule->expects(static::any())
            ->method('isEnabled')
            ->willReturn($enabled);
        $rule->expects(static::any())
            ->method('getIsSystem')
            ->willReturn($enabled);
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
            'enabled' => [
                true,
                ['enable' => ['config'], 'disable' => ['config'], 'delete' => ['config']],
                ['enable' => false, 'disable' => true, 'delete' => false],
            ],
            'disabled' => [
                false,
                ['enable' => ['config'], 'disable' => ['config'], 'delete' => ['config']],
                ['enable' => true, 'disable' => false, 'delete' => true],
            ],
        ];
    }
}
