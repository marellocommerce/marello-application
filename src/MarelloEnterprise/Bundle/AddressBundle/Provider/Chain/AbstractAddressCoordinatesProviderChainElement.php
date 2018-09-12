<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Provider\Chain;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

abstract class AbstractAddressCoordinatesProviderChainElement implements AddressCoordinatesProviderChainElementInterface
{
    /**
     * @var AddressCoordinatesProviderChainElementInterface|null
     */
    protected $successor;

    /**
     * @param AddressCoordinatesProviderChainElementInterface $provider
     * @return $this
     */
    public function setSuccessor(AddressCoordinatesProviderChainElementInterface $provider)
    {
        $this->successor = $provider;

        return $this;
    }

    /**
     * @return AddressCoordinatesProviderChainElementInterface|null
     */
    protected function getSuccessor()
    {
        return $this->successor;
    }

    /**
     * @inheritDoc
     */
    public function getCoordinates(MarelloAddress $address)
    {
        $coordinates = $this->collectCoordinates($address);
        if (!empty($coordinates)) {
            return $coordinates;
        } elseif ($this->getSuccessor()) {
            return $this->getSuccessor()->collectCoordinates($address);
        }
        
        return null;
    }
}
