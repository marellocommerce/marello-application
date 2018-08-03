<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Result\Factory\Common;

use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\ShippingMethodValidatorResultInterface;

interface CommonShippingMethodValidatorResultFactoryInterface
{
    /**
     * @return ShippingMethodValidatorResultInterface
     */
    public function createSuccessResult();

    /**
     * @param Error\Collection\ShippingMethodValidatorResultErrorCollectionInterface $errors
     *
     * @return ShippingMethodValidatorResultInterface
     */
    public function createErrorResult(
        Error\Collection\ShippingMethodValidatorResultErrorCollectionInterface $errors
    );
}
