<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceAddressProviderInterface;

class UPSShippingServiceAddressProvider implements ShippingServiceAddressProviderInterface
{
    protected $entityManager;
    
    protected $shipmentConfig;
    
    public function __construct(EntityManager $entityManager, $shipmentConfig)
    {
        $this->entityManager = $entityManager;
        $this->shipmentConfig = $shipmentConfig;
    }
    
    public function getShipFrom(ShippingAwareInterface $shippingAwareInterface)
    {
        $config = $this->shipmentConfig['order'];
    }
    
    public function getShipTo(ShippingAwareInterface $shippingAwareInterface)
    {
        $toReturn = '';
        $className = $this->getClassName($shippingAwareInterface);
        if( isset($this->shipmentConfig[$className]) ) {
            $class = $this->shipmentConfig[$className]['shipTo']['class'];
            $method = $this->shipmentConfig[$className]['shipTo']['method'];
            
            $toReturn = $shippingAwareInterface->$method();
        }
        
        return $toReturn;
    }

    private function getClassName($entity)
    {
        return $this->entityManager->getClassMetadata(get_class($entity))->getName();
    }
}