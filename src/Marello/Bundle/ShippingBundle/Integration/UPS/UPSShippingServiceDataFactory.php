<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress as MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Address;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\Package\PackagingType;
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
     * @param Registry $doctrine
     */
    public function __construct(ConfigManager $configManager, Registry $doctrine)
    {
        $this->configManager = $configManager;
        $this->doctrine      = $doctrine;
    }

    /**
     * @param ShippingServiceDataProviderInterface $shippingDataProvider
     * @return array
     */
    public function createData(ShippingServiceDataProviderInterface $shippingDataProvider)
    {
        $shipment = new Shipment();

        $shipment->rateInformation    = $this->createRateInformation();
        $shipment->description        = $this->createDescription($shippingDataProvider);
        $shipment->shipper            = $this->createShipper();
        $shipment->shipTo             = $this->createShipTo($shippingDataProvider);
        $shipment->shipFrom           = $this->createShipFrom($shippingDataProvider);
        $shipment->paymentInformation = $this->createPaymentInformation();
        $shipment->service            = $this->createService();
        $shipment->package            = $this->createPackage($shippingDataProvider);

        return compact('shipment');
    }

    /**
     * @return Shipper
     */
    protected function createShipper()
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
     * @return Service
     */
    protected function createService()
    {
        $service = new Service('11', 'UPS Standard');

        return $service;
    }

    /**
     * @param ShippingServiceDataProviderInterface $shippingDataProvider
     * @return Package
     */
    protected function createPackage(ShippingServiceDataProviderInterface $shippingDataProvider)
    {
        $package = new Package();

        $package->description     = $shippingDataProvider->getShippingDescription();
        $package->packagingType   = $packagingType = new PackagingType('02', 'Customer Supplied');
//        $package->referenceNumber = $referenceNumber = new ReferenceNumber('00', 'Package');

        $package->packageWeight = new Package\PackageWeight();
        $package->packageWeight->unitOfMeasurement = new Package\UnitOfMeasurement();
        $package->packageWeight->unitOfMeasurement->code = 'KGS';

        $weight = $shippingDataProvider->getShippingWeight();

        $package->packageWeight->weight = $weight ? (string) $weight : '1'; // Use default weight of 1 if there are no weights specified.

        return $package;
    }

    /**
     * @return PaymentInformation
     */
    private function createPaymentInformation()
    {
        $paymentInformation = new PaymentInformation();

        $paymentInformation->prepaid = $prepaid = new Prepaid();

        $prepaid->billShipper = $billShipper = new BillShipper();

        $billShipper->accountNumber = $this->configManager->get('marello_shipping.ups_account_number');

        return $paymentInformation;
    }

    /**
     * @param ShippingServiceDataProviderInterface $shippingDataProvider
     * @return string
     */
    protected function createDescription(ShippingServiceDataProviderInterface $shippingDataProvider)
    {
        return $shippingDataProvider->getShippingDescription();
    }

    /**
     * @return RateInformation
     */
    private function createRateInformation()
    {
        $rateInformation = new RateInformation();

        return $rateInformation;
    }

    private function getWarehouseAddress($model)
    {
        $warehouse = $this->doctrine->getRepository(Warehouse::class)->getDefault();

        if (!$warehouse->getAddress()) {
            return null;
        }

        $model->address       = Address::fromAddress($warehouse->getAddress());
        $model->companyName   = $this->configManager->get('marello_shipping.shipper_name');
        $model->attentionName = $warehouse->getLabel();
        $model->phoneNumber   = $warehouse->getAddress()->getPhone();

        return $model;
    }

    /**
     * @param ShippingServiceDataProviderInterface $shippingDataProvider
     * @return ShipTo
     *
     */
    protected function createShipTo(ShippingServiceDataProviderInterface $shippingDataProvider)
    {
        $shipTo = new ShipTo();

        /** @var MarelloAddress $shipToAddress */
        $shipToAddress = $shippingDataProvider->getShippingShipTo();

        $shipTo->eMailAddress = $shippingDataProvider->getShippingCustomerEmail();

        if (null === $shipToAddress) {
            return $this->getWarehouseAddress($shipTo);
        }

        $shipTo->address = Address::fromAddress($shipToAddress);
        $shipTo->companyName  = $shipToAddress->getFullName();
        $shipTo->attentionName = $shipToAddress->getFullName();
        $shipTo->phoneNumber  = $shipToAddress->getPhone();

        return $shipTo;
    }

    /**
     * @param ShippingServiceDataProviderInterface $shippingDataProvider
     * @return null
     */
    private function createShipFrom(ShippingServiceDataProviderInterface $shippingDataProvider)
    {
        $shipFrom = new ShipFrom();

        /** @var MarelloAddress $shipFromAddress */
        $shipFromAddress = $shippingDataProvider->getShippingShipFrom();

        if (null === $shipFromAddress) {
            return $this->getWarehouseAddress($shipFrom);
        }

        $shipFrom->address       = Address::fromAddress($shipFromAddress);
        $shipFrom->companyName   = $shipFromAddress->getFullName();
        $shipFrom->attentionName = $shipFromAddress->getFullName();
        $shipFrom->phoneNumber   = $shipFromAddress->getPhone();

        return $shipFrom;

    }
}
