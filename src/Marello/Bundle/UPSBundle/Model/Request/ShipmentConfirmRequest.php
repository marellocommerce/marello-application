<?php

namespace Marello\Bundle\UPSBundle\Model\Request;

class ShipmentConfirmRequest extends AbstractUPSRequest
{
    /**
     * {@inheritdoc}
     */
    public function stringify()
    {
        // Create AccessRequest XMl
        $accessRequestXML = new \SimpleXMLElement("<AccessRequest></AccessRequest>");
        $accessRequestXML->addChild("AccessLicenseNumber", $this->getAccessLicenseNumber());
        $accessRequestXML->addChild("UserId", $this->getUsername());
        $accessRequestXML->addChild("Password", $this->getPassword());

        // Create ShipmentConfirmRequest XMl
        $shipmentConfirmRequestXML = new \SimpleXMLElement("<ShipmentConfirmRequest ></ShipmentConfirmRequest>");
        $request = $shipmentConfirmRequestXML->addChild('Request');
        $request->addChild("RequestAction", "ShipConfirm");
        $request->addChild("RequestOption", "nonvalidate");

        $labelSpecification = $shipmentConfirmRequestXML->addChild('LabelSpecification');
        $labelSpecification->addChild("HTTPUserAgent", "");
        $labelPrintMethod = $labelSpecification->addChild('LabelPrintMethod');
        $labelPrintMethod->addChild("Code", "GIF");
        $labelPrintMethod->addChild("Description", "");
        $labelImageFormat = $labelSpecification->addChild('LabelImageFormat');
        $labelImageFormat->addChild("Code", "GIF");
        $labelImageFormat->addChild("Description", "");

        $shipment = $shipmentConfirmRequestXML->addChild('Shipment');
        $shipment->addChild("Description", "");
        $rateInformation = $shipment->addChild('RateInformation');
        $rateInformation->addChild("NegotiatedRatesIndicator", "");

        $shipper = $shipment->addChild('Shipper');
        $shipper->addChild("Name", $this->getShipperName());
        $shipper->addChild("ShipperNumber", $this->getShipperNumber());
        $shipperAddress = $shipper->addChild('Address');
        $shipperAddress->addChild("AddressLine1", $this->getShipperAddress()->getStreet());
        $shipperAddress->addChild("City", $this->getShipperAddress()->getCity());
        $shipperAddress->addChild("StateProvinceCode", $this->getShipperAddress()->getRegionCode());
        $shipperAddress->addChild("PostalCode", $this->getShipperAddress()->getPostalCode());
        $shipperAddress->addChild("CountryCode", $this->getShipperAddress()->getCountryIso2());

        $shipTo = $shipment->addChild('ShipTo');
        $shipTo->addChild("CompanyName", $this->getShipToName());
        $shipTo->addChild("AttentionName", "Ship to attention name");
        $shipTo->addChild("PhoneNumber", $this->getShipToAddress()->getPhone());
        $shipToAddress = $shipTo->addChild('Address');
        $shipToAddress->addChild("AddressLine1", $this->getShipToAddress()->getStreet());
        $shipToAddress->addChild("City", $this->getShipToAddress()->getCity());
        $shipToAddress->addChild("StateProvinceCode", $this->getShipToAddress()->getRegionCode());
        $shipToAddress->addChild("PostalCode", $this->getShipToAddress()->getPostalCode());
        $shipToAddress->addChild("CountryCode", $this->getShipToAddress()->getCountryIso2());

        $shipFrom = $shipment->addChild('ShipFrom');
        $shipFrom->addChild("CompanyName", $this->getShipFromName());
        $shipFrom->addChild("PhoneNumber", $this->getShipFromAddress()->getPhone());
        $shipFromAddress = $shipFrom->addChild('Address');
        $shipFromAddress->addChild("AddressLine1", $this->getShipFromAddress()->getStreet());
        $shipFromAddress->addChild("City", $this->getShipFromAddress()->getCity());
        $shipFromAddress->addChild("StateProvinceCode", $this->getShipFromAddress()->getRegionCode());
        $shipFromAddress->addChild("PostalCode", $this->getShipFromAddress()->getPostalCode());
        $shipFromAddress->addChild("CountryCode", $this->getShipFromAddress()->getCountryIso2());

        $paymentInformation = $shipment->addChild('PaymentInformation');
        $prepaid = $paymentInformation->addChild('Prepaid');
        $billShipper = $prepaid->addChild('BillShipper');
        $billShipper->addChild("AccountNumber", $this->getShipperNumber());

        $service = $shipment->addChild('Service');
        $service->addChild("Code", $this->getServiceCode());
        $service->addChild("Description", $this->getServiceDescription());

        foreach ($this->getPackages() as $packageData) {
            $package = $shipment->addChild('Package');
            $packagingType = $package->addChild('PackagingType');
            $packagingType->addChild("Code", $packageData->getPackagingTypeCode());
            $packageWeight = $package->addChild('PackageWeight');
            $packageWeight->addChild("Weight", $packageData->getWeight());
            $packageWeight->addChild('UnitOfMeasurement', $packageData->getWeightCode());
        }

        return $accessRequestXML->asXML() . $shipmentConfirmRequestXML->asXML();
    }
}
