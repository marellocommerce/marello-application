<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Marello\Bundle\OrderBundle\Entity\Customer;

class OrderCustomerAddressProvider
{
    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @param Customer|null $customer
     * @return array
     */
    public function getCustomerAddresses(Customer $customer = null)
    {
        $result = [];

        if ($customer) {
            $key = $this->getCacheKey($customer);
            if (array_key_exists($key, $this->cache)) {
                return $this->cache[$key];
            }

            $primaryAddress = $customer->getPrimaryAddress();
            
            $result[$primaryAddress->getId()] = $primaryAddress;
            foreach($customer->getAddresses() as $address) {
                $result[$address->getId()] = $address;
            }
            $this->cache[$key] = $result;

            return $result;
        } else {
            return $this->cache;
        }
    }

    /**
     * @param Customer $object
     * @return string
     */
    protected function getCacheKey($object)
    {
        $key = sprintf(
            '%s_%s_%s',
            ClassUtils::getClass($object),
            $object->getId(),
            $object->getPrimaryAddress()->getId()
        );
        foreach($object->getAddresses() as $address) {
            $key = sprintf('%s_%s', $key, $address->getId());
        }
        
        return $key;
    }
}
