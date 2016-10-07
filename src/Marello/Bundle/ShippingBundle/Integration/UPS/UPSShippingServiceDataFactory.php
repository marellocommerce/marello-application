<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress as MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
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
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSShippingServiceAddressProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class UPSShippingServiceDataFactory implements ShippingServiceDataFactoryInterface
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var Registry */
    protected $doctrine;

    /** @var  UPSShippingServiceAddressProvider */
    protected $addressProvider;

    /**
     * UPSShippingServiceDataFactory constructor.
     *
     * @param ConfigManager $configManager
     * @param Registry $doctrine
     * @param UPSShippingServiceAddressProvider $addressProvider
     */
    public function __construct(ConfigManager $configManager, Registry $doctrine, UPSShippingServiceAddressProvider $addressProvider)
    {
        $this->configManager = $configManager;
        $this->doctrine      = $doctrine;
        $this->addressProvider = $addressProvider;
    }

    /**
     * @param ShippingAwareInterface $shippingAwareInterface
     * @return array
     */
    public function createData(ShippingAwareInterface $shippingAwareInterface)
    {
        $shipment = new Shipment();

        $shipment->rateInformation    = $this->createRateInformation();
        $shipment->description        = $this->createDescription($shippingAwareInterface);
        $shipment->shipper            = $this->createShipper();
        $shipment->shipTo             = $this->createShipTo($shippingAwareInterface);
        $shipment->shipFrom           = $this->createShipFrom($shippingAwareInterface);
        $shipment->paymentInformation = $this->createPaymentInformation();
        $shipment->service            = $this->createService();
        $shipment->package            = $this->createPackage($shippingAwareInterface);

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
     * @param ShippingAwareInterface $shippingAwareInterface
     * @return Package
     */
    protected function createPackage(ShippingAwareInterface $shippingAwareInterface)
    {
        $package = new Package();

        $package->description     = $shippingAwareInterface->getShippingDescription();
        $package->packagingType   = $packagingType = new PackagingType('02', 'Customer Supplied');
//        $package->referenceNumber = $referenceNumber = new ReferenceNumber('00', 'Package');

        $package->packageWeight = new Package\PackageWeight();
        $package->packageWeight->unitOfMeasurement = new Package\UnitOfMeasurement();
        $package->packageWeight->unitOfMeasurement->code = 'KGS';

        $weight = $shippingAwareInterface->getShippingWeight();

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
     * @param ShippingAwareInterface $shippingAwareInterface
     * @return string
     */
    protected function createDescription(ShippingAwareInterface $shippingAwareInterface)
    {
        return $shippingAwareInterface->getShippingDescription();
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
     * @param ShippingAwareInterface $shippingAwareInterface
     * @return ShipTo
     *
     */
    protected function createShipTo(ShippingAwareInterface $shippingAwareInterface)
    {
        $shipToAddress = $this->addressProvider->getShipTo($shippingAwareInterface);

        $shipTo = new ShipTo();

//        /** @var MarelloAddress $shippingAddress */
//        $shippingAddress = $shippingAwareInterface->getShipTo();

        $shipTo->companyName  = $shipToAddress->getFullName();
        $shipTo->attentionName = $shipToAddress->getFullName();
        $shipTo->phoneNumber  = $shipToAddress->getPhone();

        //TODO get address of warehouse in returnEntity
//        $shipTo->eMailAddress = $shippingAwareInterface->getCustomer()->getEmail();
        $shipTo->eMailAddress = 'joey@madia.nl';

        $shipTo->address = Address::fromAddress($shipToAddress);

        return $shipTo;
    }

    //TODO: $shippingAwareInterface->getShipFrom() link info and get from the entity

    /**
     * @param ShippingAwareInterface $shippingAwareInterface
     * @return null
     */
    private function createShipFrom(ShippingAwareInterface $shippingAwareInterface)
    {
        $shipFromAddress = $this->addressProvider->getShipFrom($shippingAwareInterface);

        $shipFrom = new ShipFrom();

        if ($shippingAwareInterface instanceOf \Marello\Bundle\ReturnBundle\Entity\ReturnEntity) {

//            /** @var MarelloAddress $shipFromAddress */
//            $shipFromAddress = $shippingAwareInterface->getShipFrom();

            $shipFrom->address       = Address::fromAddress($shipFromAddress);
            $shipFrom->companyName   = $shipFromAddress->getFullName();
            $shipFrom->attentionName = $shipFromAddress->getFullName();
            $shipFrom->phoneNumber   = $shipFromAddress->getPhone();

        } else {
            $warehouse = $this->doctrine->getRepository(Warehouse::class)->getDefault();

            if (!$warehouse->getAddress()) {
                return null;
            }

            $shipFrom->address       = Address::fromAddress($warehouse->getAddress());
            $shipFrom->companyName   = $this->configManager->get('marello_shipping.shipper_name');
            $shipFrom->phoneNumber   = $warehouse->getAddress()->getPhone();
            $shipFrom->attentionName = $warehouse->getLabel();
        }



    }
}
