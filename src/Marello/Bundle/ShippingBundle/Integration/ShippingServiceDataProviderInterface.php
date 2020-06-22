<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

interface ShippingServiceDataProviderInterface
{
    /**
     * @return MarelloAddress | null
     */
    public function getShippingShipTo();

    /**
     * @return MarelloAddress | null
     */
    public function getShippingShipFrom();

    /**
     * @return string
     */
    public function getShippingCustomerEmail();

    /**
     * @return string
     */
    public function getShippingWeight();

    /**
     * @return string
     */
    public function getShippingDescription();

    /**
     * @param $entity
     * @return ShippingServiceDataProviderInterface
     */
    public function setEntity($entity);

    /**
     * @return mixed
     */
    public function getEntity();
}
