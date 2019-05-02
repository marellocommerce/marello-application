<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Result;

use Marello\Bundle\ShippingBundle\Method\Validator\Result;

interface ShippingMethodValidatorResultInterface
{
    /**
     * @return Result\Factory\Common\CommonShippingMethodValidatorResultFactoryInterface
     */
    public function createCommonFactory();

    /**
     * @return Result\Error\Collection\ShippingMethodValidatorResultErrorCollectionInterface
     */
    public function getErrors();
}
