<?php

namespace Marello\Bundle\ReturnBundle\Tests\Unit\Validator;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\ReturnBundle\Validator\Constraints\ReturnItemConstraint;
use Marello\Bundle\ReturnBundle\Validator\ReturnItemValidator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ReturnItemValidatorTest extends TestCase
{
    /** @var ReturnItemValidator */
    protected $validator;

    /** @var ConstraintViolationBuilderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $builder;

    public function setUp()
    {
        parent::setUp();

        $this->builder = $this
            ->getMockForAbstractClass(ConstraintViolationBuilderInterface::class);

        $this->builder->expects($this->any())
            ->method('atPath')
            ->will($this->returnValue($this->builder));

        $context = $this->getMockForAbstractClass(ExecutionContextInterface::class);

        $context->expects($this->any())
            ->method('buildViolation')
            ->will($this->returnValue($this->builder));

        $this->validator = new ReturnItemValidator();
        $this->validator->initialize($context);
    }

    /**
     * @param array $distribution
     * @param int   $total
     *
     * @return ReturnItem
     */
    protected function getItem($distribution = [10], $total = 10)
    {
        $orderItem = new OrderItem();
        $orderItem->setQuantity($total);

        $testedItem = null;

        foreach ($distribution as $q) {
            $returnItem = new ReturnItem($orderItem);
            $returnItem->setQuantity($q);

            if ($testedItem) {
                $orderItem->getReturnItems()->add($returnItem);
            } else {
                $testedItem = $returnItem;
            }
        }

        return $testedItem;
    }

    public function validateDataProvider()
    {
        return [
            'VALID: One Return item with same quantity as ordered'      => [$this->getItem(), true],
            'VALID: One return item with quantity less then ordered'    => [$this->getItem([5]), true],
            'VALID: Two return items with same quantity as ordered'     => [$this->getItem([5, 5]), true],
            'VALID: Two return items with lower quantity as ordered'    => [$this->getItem([2, 3]), true],
            'INVALID: One return item with quantity more than ordered'  => [$this->getItem([14]), false],
            'INVALID: Two return items with quantity more than ordered' => [$this->getItem([7, 6]), false],
        ];
    }

    /**
     * @dataProvider validateDataProvider
     *
     * @param ReturnItem $item
     * @param bool       $valid
     */
    public function testValidate(ReturnItem $item, $valid)
    {
        if ($valid) {
            $this->builder
                ->expects($this->never())
                ->method('addViolation');
        } else {
            $this->builder
                ->expects($this->once())
                ->method('addViolation');
        }

        $this->validator->validate($item, new ReturnItemConstraint());
    }
}
