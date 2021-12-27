<?php

namespace Marello\Bundle\CustomerBundle\Tests\Unit\Provider;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;

use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CustomerBundle\Provider\CustomerAddressProvider;

class CustomerAddressProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var CustomerAddressProvider
     */
    protected $customerAddressProvider;

    protected function setUp(): void
    {
        $this->customerAddressProvider = new CustomerAddressProvider();
    }

    /**
     * {@inheritdoc}
     */
    public function testGetCustomerAddress()
    {
        $primaryAddress = $this->getEntity(MarelloAddress::class, ['id' => 7]);
        $customer = $this->getEntity(Customer::class, ['id' => 1, 'primaryAddress' => $primaryAddress]);

        static::assertEquals(
            [7 => $primaryAddress],
            $this->customerAddressProvider->getCustomerBillingAddresses($customer)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testGetCustomerPrimaryAddress()
    {
        $primaryAddress = $this->getEntity(MarelloAddress::class, ['id' => 7]);
        $customer = $this->getEntity(Customer::class, ['id' => 1, 'primaryAddress' => $primaryAddress]);

        static::assertEquals(
            [7 => $primaryAddress],
            $this->customerAddressProvider->getCustomerBillingAddresses($customer)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testGetCustomerShippingAddress()
    {
        $shippingAddress = $this->getEntity(MarelloAddress::class, ['id' => 8]);
        $customer = $this->getEntity(Customer::class, ['id' => 1, 'shippingAddress' => $shippingAddress]);

        static::assertEquals(
            [8 => $shippingAddress],
            $this->customerAddressProvider->getCustomerShippingAddresses($customer)
        );
    }
}
