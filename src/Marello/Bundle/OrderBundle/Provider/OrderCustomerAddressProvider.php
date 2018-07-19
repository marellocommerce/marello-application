<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Marello\Bundle\OrderBundle\Entity\Customer;

class OrderCustomerAddressProvider
{
    const CACHE_KEY_BILLING = 'billing';
    const CACHE_KEY_SHIPPING = 'shipping';

    /**
     * @var array
     */
    protected $cache = [
        self::CACHE_KEY_BILLING => [],
        self::CACHE_KEY_SHIPPING => [],
    ];

    /**
     * @param Customer|null $customer
     * @return array
     * @deprecated since version 1.4.0 use getCustomerBillingAddresses() instead
     */
    public function getCustomerAddresses(Customer $customer = null)
    {
        return $this->getCustomerBillingAddresses($customer);
    }

    /**
     * @param Customer|null $customer
     * @return array
     */
    public function getCustomerBillingAddresses(Customer $customer = null)
    {
        $result = [];

        if ($customer) {
            $key = $this->getCacheKey($customer);
            if (array_key_exists($key, $this->cache[self::CACHE_KEY_BILLING])) {
                return $this->cache[self::CACHE_KEY_BILLING][$key];
            }

            $primaryAddress = $customer->getPrimaryAddress();

            $result[$primaryAddress->getId()] = $primaryAddress;

            foreach ($customer->getAddresses() as $address) {
                $result[$address->getId()] = $address;
            }
            $this->cache[self::CACHE_KEY_BILLING][$key] = $result;

            return $result;
        } else {
            return $this->cache[self::CACHE_KEY_BILLING];
        }
    }

    /**
     * @param Customer|null $customer
     * @return array
     */
    public function getCustomerShippingAddresses(Customer $customer = null)
    {
        $result = [];

        if ($customer) {
            $key = $this->getCacheKey($customer);
            if (array_key_exists($key, $this->cache[self::CACHE_KEY_SHIPPING])) {
                return $this->cache[self::CACHE_KEY_SHIPPING][$key];
            }

            $shippingAddress = $customer->getShippingAddress();
            if ($shippingAddress) {
                $result[$shippingAddress->getId()] = $shippingAddress;
            }

            foreach ($customer->getAddresses() as $address) {
                $result[$address->getId()] = $address;
            }
            $this->cache[self::CACHE_KEY_SHIPPING][$key] = $result;

            return $result;
        } else {
            return $this->cache[self::CACHE_KEY_SHIPPING];
        }
    }

    /**
     * @param Customer $object
     * @return string
     */
    protected function getCacheKey($object)
    {
        return sprintf(
            '%s_%s',
            ClassUtils::getClass($object),
            $object->getId()
        );
    }
}
