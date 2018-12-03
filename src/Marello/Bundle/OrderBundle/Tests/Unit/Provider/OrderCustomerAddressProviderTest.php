<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;

use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Provider\OrderCustomerAddressProvider;

class OrderCustomerAddressProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var OrderCustomerAddressProvider
     */
    protected $orderCustomerAddressProvider;

    protected function setUp()
    {
        $this->orderCustomerAddressProvider = new OrderCustomerAddressProvider();
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
            $this->orderCustomerAddressProvider->getCustomerAddresses($customer)
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
            $this->orderCustomerAddressProvider->getCustomerBillingAddresses($customer)
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
            $this->orderCustomerAddressProvider->getCustomerShippingAddresses($customer)
        );
    }
}
