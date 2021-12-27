<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Matcher;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\TaxBundle\Entity\Repository\TaxRuleRepository;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface;

abstract class AbstractTaxRuleMatcherTest extends TestCase
{
    use EntityTrait;
    
    /**
     * @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $doctrineHelper;

    /**
     * @var TaxRuleMatcherInterface
     */
    protected $matcher;

    /**
     * @var TaxRuleRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $taxRuleRepository;

    protected function setUp(): void
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

    protected function tearDown(): void
    {
        unset($this->matcher, $this->doctrineHelper, $this->taxRuleRepository);
    }

    /**
     * @param int $id
     * @return TaxRule|object
     */
    protected function getTaxRule($id)
    {
        return $this->getEntity(TaxRule::class, ['id' => $id]);
    }
}
