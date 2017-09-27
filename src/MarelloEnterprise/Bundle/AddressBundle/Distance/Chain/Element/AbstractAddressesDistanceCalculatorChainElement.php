<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Distance\AddressesDistanceCalculatorInterface;

abstract class AbstractAddressesDistanceCalculatorChainElement implements AddressesDistanceCalculatorInterface
{
    /**
     * @var AddressesDistanceCalculatorInterface|null
     */
    protected $successor;

    /**
     * @param AddressesDistanceCalculatorInterface $distanceCalculator
     * @return $this
     */
    public function setSuccessor(AddressesDistanceCalculatorInterface $distanceCalculator)
    {
        $this->successor = $distanceCalculator;
        
        return $this;
    }

    /**
     * @return AddressesDistanceCalculatorInterface|null
     */
    protected function getSuccessor()
    {
        return $this->successor;
    }
    
    /**
     * {@inheritdoc}
     */
    public function calculate(MarelloAddress $originAddress, MarelloAddress $destinationAddress)
    {
        $distance = round($this->getDistance($originAddress, $destinationAddress), 2);
        
        if ($distance) {
            return $distance;
        } elseif ($this->getSuccessor()) {
            return $this->getSuccessor()->calculate($originAddress, $destinationAddress);
        }
        
        throw new \Exception(
            sprintf(
                'Not possible to calculate distance between %s and %s',
                $originAddress,
                $destinationAddress
            )
        );
    }

    /**
     * @param MarelloAddress $originAddress
     * @param MarelloAddress $destinationAddress
     * @param string $unit
     * @return float
     */
    abstract protected function getDistance(
        MarelloAddress $originAddress,
        MarelloAddress $destinationAddress,
        $unit = 'metric'
    );
}
