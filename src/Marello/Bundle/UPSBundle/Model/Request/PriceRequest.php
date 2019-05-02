<?php

namespace Marello\Bundle\UPSBundle\Model\Request;

use Marello\Bundle\UPSBundle\Model\Package;

class PriceRequest extends AbstractUPSRequest
{
    /**
     * @var string
     */
    protected $requestOption;

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return string
     */
    public function stringify()
    {
        $request = [
            'UPSSecurity' => [
                'UsernameToken' => [
                    'Username' => $this->username,
                    'Password' => $this->password,
                ],
                'ServiceAccessToken' => [
                    'AccessLicenseNumber' => $this->accessLicenseNumber,
                ],
            ],
            'RateRequest' => [
                'Request' => [
                    'RequestOption' => $this->requestOption,
                ],
                'Shipment' => [
                    'Shipper' => [
                        'Name' => $this->shipperName,
                        'ShipperNumber' => $this->shipperNumber,
                        'Address' => $this->shipperAddress ? [
                            'AddressLine' => [
                                $this->shipperAddress->getStreet(),
                                $this->shipperAddress->getStreet2()
                            ],
                            'City' => $this->shipperAddress->getCity(),
                            'StateProvinceCode' => $this->shipperAddress->getRegionCode(),
                            'PostalCode' => $this->shipperAddress->getPostalCode(),
                            'CountryCode' => $this->shipperAddress->getCountryIso2(),
                        ] : [],
                    ],
                    'ShipTo' => [
                        'Name' => $this->shipToName,
                        'Address' => $this->shipToAddress ? [
                            'AddressLine' => [
                                $this->shipToAddress->getStreet(),
                                $this->shipToAddress->getStreet2()
                            ],
                            'City' => $this->shipToAddress->getCity(),
                            'StateProvinceCode' => $this->shipToAddress->getRegionCode(),
                            'PostalCode' => $this->shipToAddress->getPostalCode(),
                            'CountryCode' => $this->shipToAddress->getCountryIso2(),
                        ] : [],
                    ],
                    'ShipFrom' => [
                        'Name' => $this->shipFromName,
                        'Address' => $this->shipFromAddress ? [
                            'AddressLine' => [
                                $this->shipFromAddress->getStreet(),
                                $this->shipFromAddress->getStreet2()
                            ],
                            'City' => $this->shipFromAddress->getCity(),
                            'StateProvinceCode' => $this->shipFromAddress->getRegionCode(),
                            'PostalCode' => $this->shipFromAddress->getPostalCode(),
                            'CountryCode' => $this->shipFromAddress->getCountryIso2(),
                        ] : [],
                    ],
                    'Package' => array_map(function (Package $package) {
                        return $package->toArray();
                    }, $this->packages),
                ],
            ],
        ];

        if ($this->getServiceCode() && $this->getServiceDescription()) {
            $request['RateRequest']['Shipment']['Service'] = [
                'Code' => $this->serviceCode,
                'Description' => $this->serviceDescription,
            ];
        }

        return json_encode($request);
    }

    /**
     * @return string
     */
    public function getRequestOption()
    {
        return $this->requestOption;
    }

    /**
     * @param string $requestOption
     * @return $this
     */
    public function setRequestOption($requestOption)
    {
        $this->requestOption = $requestOption;

        return $this;
    }
}
