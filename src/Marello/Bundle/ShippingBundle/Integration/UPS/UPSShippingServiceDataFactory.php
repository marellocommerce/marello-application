<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\AddressBundle\Entity\Address as MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Address;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package\PackagingType;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package\ReferenceNumber;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\PaymentInformation;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\PaymentInformation\BillShipper;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\PaymentInformation\Prepaid;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\RateInformation;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Service;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\ShipFrom;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipment;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Shipper;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\ShipTo;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class UPSShippingServiceDataFactory implements ShippingServiceDataFactoryInterface
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var Registry */
    protected $doctrine;

    /**
     * UPSShippingServiceDataFactory constructor.
     *
     * @param ConfigManager $configManager
     * @param Registry      $doctrine
     */
    public function __construct(ConfigManager $configManager, Registry $doctrine)
    {
        $this->configManager = $configManager;
        $this->doctrine      = $doctrine;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    public function createData(Order $order)
    {
        $shipment = new Shipment();

        $shipment->rateInformation    = $this->createRateInformation($order);
        $shipment->description        = $this->createDescription($order);
        $shipment->shipper            = $this->createShipper($order);
        $shipment->shipTo             = $this->createShipTo($order);
        $shipment->shipFrom           = $this->createShipFrom($order);
        $shipment->paymentInformation = $this->createPaymentInformation($order);
        $shipment->service            = $this->createService($order);
        $shipment->package            = $this->createPackage($order);

        return compact('shipment');
    }

    /**
     * @param Order $order
     *
     * @return Shipper
     */
    protected function createShipper(Order $order)
    {
        $shipper = new Shipper();

        $shipper->name                    = $this->configManager->get('marello_shipping.shipper_name');
        $shipper->attentionName           = $this->configManager->get('marello_shipping.shipper_attention_name');
        $shipper->phoneNumber             = $this->configManager->get('marello_shipping.shipper_phone');
        $shipper->taxIdentificationNumber = $this->configManager->get('marello_shipping.shipper_tax_id');
        $shipper->eMailAddress            = $this->configManager->get('marello_shipping.shipper_email');
        $shipper->shipperNumber           = $this->configManager->get('marello_shipping.ups_account_number');

        $shipper->address = new Address();

        $shipper->address->addressLine1      = $this->configManager->get('marello_shipping.shipper_address_line_1');
        $shipper->address->addressLine2      = $this->configManager->get('marello_shipping.shipper_address_line_2');
        $shipper->address->addressLine3      = $this->configManager->get('marello_shipping.shipper_address_line_3');
        $shipper->address->city              = $this->configManager->get('marello_shipping.shipper_address_city');
        $shipper->address->stateProvinceCode = $this->configManager->get('marello_shipping.shipper_address_state');
        $shipper->address->postalCode        = $this->configManager->get('marello_shipping.shipper_address_postal_code');
        $shipper->address->countryCode       = $this->configManager->get('marello_shipping.shipper_address_country_code');

        return $shipper;
    }

    /**
     * @param Order $order
     *
     * @return ShipTo
     */
    protected function createShipTo(Order $order)
    {
        $shipTo = new ShipTo();

        /** @var MarelloAddress $shippingAddress */
        $shippingAddress = $order->getShippingAddress();

        $shipTo->companyName  = $shippingAddress->getFullName();
        $shipTo->attentionName = $shippingAddress->getFullName();
        $shipTo->phoneNumber  = $shippingAddress->getPhone();
        $shipTo->eMailAddress = $order->getCustomer()->getEmail();

        $shipTo->address = $address = Address::fromAddress($shippingAddress);

        return $shipTo;
    }

    /**
     * @param Order $order
     *
     * @return Service
     */
    protected function createService(Order $order)
    {
        $service = new Service();

        $service->code        = '11'; // TODO: Figure out how to determine service
        $service->description = 'UPS Standard';

        return $service;
    }

    /**
     * @param Order $order
     *
     * @return Package
     */
    protected function createPackage(Order $order)
    {
        $package = new Package();

        $package->description     = 'Package Description';
        $package->packagingType   = $packagingType = new PackagingType('02', 'Customer Supplied');
//        $package->referenceNumber = $referenceNumber = new ReferenceNumber('00', 'Package');
        
        $package->packageWeight = new Package\PackageWeight();
        $package->packageWeight->unitOfMeasurement = new Package\UnitOfMeasurement();
        $package->packageWeight->unitOfMeasurement->code = 'KGS';
        $package->packageWeight->weight = '1'; // TODO:

        return $package;
    }

    /**
     * @param Order $order
     *
     * @return PaymentInformation
     */
    private function createPaymentInformation(Order $order)
    {
        $paymentInformation = new PaymentInformation();

        $paymentInformation->prepaid = $prepaid = new Prepaid();

        $prepaid->billShipper = $billShipper = new BillShipper();

        $billShipper->accountNumber = $this->configManager->get('marello_shipping.ups_account_number');

        return $paymentInformation;
    }

    /**
     * @param Order $order
     *
     * @return null
     */
    protected function createDescription(Order $order)
    {
        return null; // TODO: Create description
    }

    /**
     * @param Order $order
     *
     * @return RateInformation
     */
    private function createRateInformation(Order $order)
    {
        $rateInformation = new RateInformation();

        return $rateInformation;
    }

    /**
     * @param Order $order
     *
     * @return null
     */
    private function createShipFrom(Order $order)
    {
        $warehouse = $this->doctrine->getRepository(Warehouse::class)->getDefault();

        if (!$warehouse->getAddress()) {
            return null;
        }

        $shipFrom = new ShipFrom();

        $shipFrom->address       = Address::fromAddress($warehouse->getAddress());
        $shipFrom->companyName   = $this->configManager->get('marello_shipping.shipper_name');
        $shipFrom->phoneNumber   = $warehouse->getAddress()->getPhone();
        $shipFrom->attentionName = $warehouse->getLabel();
    }
}
