<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Basic;

use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\Factory\Common;
use Marello\Bundle\ShippingBundle\Method\Validator\ShippingMethodValidatorInterface;

class BasicShippingMethodValidator implements ShippingMethodValidatorInterface
{
    /**
     * @var Common\CommonShippingMethodValidatorResultFactoryInterface
     */
    private $commonShippingMethodValidatorResultFactory;

    /**
     * @param Common\CommonShippingMethodValidatorResultFactoryInterface $commonShippingMethodValidatorResultFactory
     */
    public function __construct(
        Common\CommonShippingMethodValidatorResultFactoryInterface $commonShippingMethodValidatorResultFactory
    ) {
        $this->commonShippingMethodValidatorResultFactory = $commonShippingMethodValidatorResultFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(ShippingMethodInterface $shippingMethod)
    {
        return $this->commonShippingMethodValidatorResultFactory->createSuccessResult();
    }
}
