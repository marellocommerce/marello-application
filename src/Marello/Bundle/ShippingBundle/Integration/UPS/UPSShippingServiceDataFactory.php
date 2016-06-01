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

class UPSShippingServiceDataFactory implements ShippingServiceDataFactoryInterface
{

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

        $shipper->name                    = 'TEMP'; // TODO: Name
        $shipper->attentionName           = 'Temp Attention Name'; // TODO: Attention Name
        $shipper->companyDisplayableName  = 'Company Displayable Name'; // TODO: Company Displayable Name
        $shipper->number                  = '123456789'; // TODO: Number
        $shipper->taxIdentificationNumber = '123456789';
        $shipper->phoneNumber             = '+012 345 678 910';
        $shipper->eMailAddress            = 'shipper@example.com';

        $shipper->address = new Address();

        $shipper->address->addressLine1 = 'TEMP'; // TODO: Address Line 1
        $shipper->address->addressLine2 = 'TEMP'; // TODO: Address Line 2
        $shipper->address->addressLine3 = 'TEMP'; // TODO: Address Line 3

        $shipper->address->city              = 'TEMP'; // TODO: City
        $shipper->address->stateProvinceCode = 'TEMP'; // TODO: State Province Code
        $shipper->address->postalCode        = '12345'; // TODO: Postal Code
        $shipper->address->countryCode       = 'US'; // TODO: Country Code

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
