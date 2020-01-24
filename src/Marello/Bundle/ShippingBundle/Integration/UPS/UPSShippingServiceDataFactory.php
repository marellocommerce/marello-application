<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;
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

        $shipper->name                    = $this->getConfigValue('marello_shipping.shipper_name');
        $shipper->attentionName           = $this->getConfigValue('marello_shipping.shipper_attention_name');
        $shipper->phoneNumber             = $this->getConfigValue('marello_shipping.shipper_phone');
        $shipper->taxIdentificationNumber = $this->getConfigValue('marello_shipping.shipper_tax_id');
        $shipper->eMailAddress            = $this->getConfigValue('marello_shipping.shipper_email');
        $shipper->shipperNumber           = $this->getConfigValue('marello_shipping.ups_account_number');

        $shipper->address = new Address();

        $shipper->address->addressLine1      = $this->getConfigValue('marello_shipping.shipper_address_line_1');
        $shipper->address->addressLine2      = $this->getConfigValue('marello_shipping.shipper_address_line_2');
        $shipper->address->addressLine3      = $this->getConfigValue('marello_shipping.shipper_address_line_3');
        $shipper->address->city              = $this->getConfigValue('marello_shipping.shipper_address_city');
        $shipper->address->stateProvinceCode = $this->getConfigValue('marello_shipping.shipper_address_state');
        $shipper->address->postalCode        = $this->getConfigValue('marello_shipping.shipper_address_postal_code');
        $shipper->address->countryCode       = $this->getConfigValue('marello_shipping.shipper_address_country_code');

        return $shipper;
    }

    /**
     * Get config value from manager
     * @param $value
     * @return mixed|null
     */
    protected function getConfigValue($value)
    {
        if (!$value) {
            return null;
        }

        return $this->configManager->get($value);
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
        $package->packageWeight = new Package\PackageWeight();
        $package->packageWeight->unitOfMeasurement = new Package\UnitOfMeasurement();
        $package->packageWeight->unitOfMeasurement->code = 'KGS';

        $weight = $shippingDataProvider->getShippingWeight();

        // Use default weight of 1 if there are no weights specified.
        $package->packageWeight->weight = $weight ? (string) $weight : '1';

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

        $shipTo->address = Address::fromAddress($shipToAddress);

        $shipTo->companyName  = ($shipToAddress->getCompany()) ?
            $shipToAddress->getCompany() : $shipToAddress->getFullName();
        $shipTo->attentionName = ($shipToAddress->getFullName()) ?
            $shipToAddress->getFullName() : $shipToAddress->getCompany();
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

        $shipFrom->address       = Address::fromAddress($shipFromAddress);
        $shipFrom->companyName   = ($shipFromAddress->getCompany()) ?
            $shipFromAddress->getCompany() : $shipFromAddress->getFullName();
        $shipFrom->attentionName = ($shipFromAddress->getFullName()) ?
            $shipFromAddress->getFullName() : $shipFromAddress->getCompany();
        $shipFrom->phoneNumber   = $shipFromAddress->getPhone();

        return $shipFrom;
    }
}
