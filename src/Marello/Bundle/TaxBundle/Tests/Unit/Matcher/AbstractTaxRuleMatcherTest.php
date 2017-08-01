<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Matcher;

use Marello\Bundle\TaxBundle\Entity\Repository\TaxRuleRepository;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\Unit\EntityTrait;

abstract class AbstractTaxRuleMatcherTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;
    
    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var TaxRuleMatcherInterface
     */
    protected $matcher;

    /**
     * @var TaxRuleRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRuleRepository;

    protected function setUp()
    {
        $this->taxRuleRepository = $this
            ->getMockBuilder(TaxRuleRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper = $this
            ->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepositoryForClass')
            ->with(TaxRule::class)
            ->willReturn($this->taxRuleRepository);
    }

    protected function tearDown()
    {
        unset($this->matcher, $this->doctrineHelper, $this->taxRuleRepository);
    }

    /**
     * @param int $id
     * @return TaxRule
     */
    protected function getTaxRule($id)
    {
        return $this->getEntity(TaxRule::class, ['id' => $id]);
    }
}
