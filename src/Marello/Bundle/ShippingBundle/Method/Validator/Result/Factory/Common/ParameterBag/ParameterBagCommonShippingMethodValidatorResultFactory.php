<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Result\Factory\Common\ParameterBag;

use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\Factory;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\ParameterBag\ParameterBagShippingMethodValidatorResult;

class ParameterBagCommonShippingMethodValidatorResultFactory implements
    Factory\Common\CommonShippingMethodValidatorResultFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createSuccessResult()
    {
        return new ParameterBagShippingMethodValidatorResult(
            [
                ParameterBagShippingMethodValidatorResult::FIELD_ERRORS =>
                    new Error\Collection\Doctrine\DoctrineShippingMethodValidatorResultErrorCollection(),
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createErrorResult(
        Error\Collection\ShippingMethodValidatorResultErrorCollectionInterface $errors
    ) {
        return new ParameterBagShippingMethodValidatorResult(
            [
                ParameterBagShippingMethodValidatorResult::FIELD_ERRORS => $errors,
            ]
        );
    }
}
