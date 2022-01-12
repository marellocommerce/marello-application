<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Method\Validator\Basic;

use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\Validator\Basic\BasicShippingMethodValidator;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\Factory\Common;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\ShippingMethodValidatorResultInterface;

class BasicShippingMethodValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Common\CommonShippingMethodValidatorResultFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $commonShippingMethodValidatorResultFactory;

    /**
     * @var BasicShippingMethodValidator
     */
    private $validator;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->commonShippingMethodValidatorResultFactory = $this->createMock(
            Common\CommonShippingMethodValidatorResultFactoryInterface::class
        );

        $this->validator = new BasicShippingMethodValidator($this->commonShippingMethodValidatorResultFactory);
    }

    public function testValidate()
    {
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethodInterface::class);

        $result = $this->createMock(ShippingMethodValidatorResultInterface::class);

        $this->commonShippingMethodValidatorResultFactory->expects(static::once())
            ->method('createSuccessResult')
            ->willReturn($result);

        static::assertSame($result, $this->validator->validate($shippingMethod));
    }
}
