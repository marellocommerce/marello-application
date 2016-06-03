<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Marello\Bundle\AddressBundle\Entity\Address as MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Address;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Service;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipment;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipper;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\ShipTo;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class UPSShippingServiceDataFactory implements ShippingServiceDataFactoryInterface
{

    /** @var ConfigManager */
    protected $configManager;

    /**
     * UPSShippingServiceDataFactory constructor.
     *
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    public function createData(Order $order)
    {
        $shipment = new Shipment();

        $shipment->shipper = $this->createShipper($order);
        $shipment->shipTo  = $this->createShipTo($order);
        $shipment->service = $this->createService($order);
        $shipment->package = new Package();

        return compact('shipment');
    }

    protected function createShipper(Order $order)
    {
        $shipper = new Shipper();

        $shipper->name                    = $this->configManager->get('marello_shipping.shipper_name');
        $shipper->attentionName           = $this->configManager->get('marello_shipping.shipper_attention_name');
        $shipper->shipperNumber           = $this->configManager->get('marello_shipping.ups_account_number');
        $shipper->taxIdentificationNumber = $this->configManager->get('marello_shipping.shipper_tax_id');
        $shipper->phoneNumber             = $this->configManager->get('marello_shipping.shipper_phone');
        $shipper->eMailAddress            = $this->configManager->get('marello_shipping.shipper_email');

        $shipper->address = new Address();

        $shipper->address->addressLine1 = $this->configManager->get('marello_shipping.shipper_address_line_1');
        $shipper->address->addressLine2 = $this->configManager->get('marello_shipping.shipper_address_line_2');
        $shipper->address->addressLine3 = $this->configManager->get('marello_shipping.shipper_address_line_3');

        $shipper->address->city              = $this->configManager->get('marello_shipping.shipper_address_city');
        $shipper->address->stateProvinceCode = $this->configManager->get('marello_shipping.shipper_address_state');
        $shipper->address->postalCode        = $this->configManager->get('marello_shipping.shipper_address_postal_code');
        $shipper->address->countryCode       = $this->configManager->get('marello_shipping.shipper_address_city_code');

        return $shipper;
    }

    protected function createShipTo(Order $order)
    {
        $shipTo = new ShipTo();

        /** @var MarelloAddress $shippingAddress */
        $shippingAddress = $order->getShippingAddress();

        $shipTo->companyName  = $shippingAddress->getFullName();
        $shipTo->phoneNumber  = $shippingAddress->getPhone();
        $shipTo->eMailAddress = $order->getCustomer()->getEmail();

        $shipTo->address = $address = Address::fromAddress($shippingAddress);

        return $shipTo;
    }

    protected function createService(Order $order)
    {
        $service = new Service();

        return $service;
    }

    protected function createPackage(Order $order)
    {
        $package = new Package();

        return $package;
    }
}
