<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Factory;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\TaxBundle\Factory\TaxFactory;
use Marello\Bundle\TaxBundle\Mapper\TaxMapperInterface;
use Marello\Bundle\TaxBundle\Model\Taxable;

class TaxFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = new TaxFactory();
    }

    protected function tearDown()
    {
        unset($this->factory);
    }

    public function testAddMapperAndCreate()
    {
        /** @var TaxMapperInterface|\PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->createMock(TaxMapperInterface::class);
        $mapper
            ->expects($this->once())
            ->method('getProcessingClassName')
            ->willReturn(Order::class);

        $mapper
            ->expects($this->exactly(2))
            ->method('map')
            ->willReturnCallback(
                function () {
                    return new Taxable();
                }
            );

        $this->factory->addMapper($mapper);
        $object = new Order();

        $object->setSubtotal(45.5);
        $taxable = $this->factory->create($object);
        $this->assertInstanceOf(Taxable::class, $taxable);

        $object->setSubtotal(50);
        $anotherTaxable = $this->factory->create($object);

        $this->assertInstanceOf(Taxable::class, $anotherTaxable);
        $this->assertNotSame($taxable, $anotherTaxable);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Can't find Tax Mapper for object "stdClass"
     */
    public function testCreateThrowExceptionWithoutMapper()
    {
        $this->factory->create(new \stdClass());
    }

    public function testSupports()
    {
        $this->assertFalse($this->factory->supports(new \stdClass()));

        /** @var TaxMapperInterface|\PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->createMock(TaxMapperInterface::class);
        $mapper
            ->expects($this->once())
            ->method('getProcessingClassName')
            ->willReturn('stdClass');

        $this->factory->addMapper($mapper);
        $this->assertTrue($this->factory->supports(new \stdClass()));
    }
}
