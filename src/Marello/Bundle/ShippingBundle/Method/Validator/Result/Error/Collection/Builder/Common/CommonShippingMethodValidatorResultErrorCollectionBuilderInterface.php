<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\Collection\Builder\Common;

use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error;

interface CommonShippingMethodValidatorResultErrorCollectionBuilderInterface
{
    /**
     * @return Error\Collection\ShippingMethodValidatorResultErrorCollectionInterface
     */
    public function getCollection();

    /**
     * @param Error\Collection\ShippingMethodValidatorResultErrorCollectionInterface $collection
     *
     * @return $this
     */
    public function cloneAndBuild(
        Error\Collection\ShippingMethodValidatorResultErrorCollectionInterface $collection
    );

    /**
     * @param Error\ShippingMethodValidatorResultErrorInterface $error
     *
     * @return $this
     */
    public function addError(Error\ShippingMethodValidatorResultErrorInterface $error);
}
