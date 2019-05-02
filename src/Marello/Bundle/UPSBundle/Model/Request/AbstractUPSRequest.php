<?php

namespace Marello\Bundle\UPSBundle\Model\Request;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\UPSBundle\Model\Package;

abstract class AbstractUPSRequest implements UPSRequestInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $accessLicenseNumber;

    /**
     * @var string
     */
    protected $serviceDescription;

    /**
     * @var string
     */
    protected $serviceCode;

    /**
     * @var string
     */
    protected $shipperName;

    /**
     * @var string
     */
    protected $shipperNumber;

    /**
     * @var MarelloAddress
     */
    protected $shipperAddress;

    /**
     * @var string
     */
    protected $shipFromName;

    /**
     * @var MarelloAddress
     */
    protected $shipFromAddress;

    /**
     * @var string
     */
    protected $shipToName;

    /**
     * @var MarelloAddress
     */
    protected $shipToAddress;

    /**
     * @var Package[]
     */
    protected $packages = [];


    /**
     * @param $username
     * @param $password
     * @param $accessLicenseNumber
     * @return $this
     */
    public function setSecurity($username, $password, $accessLicenseNumber)
    {
        $this->username = $username;
        $this->password = $password;
        $this->accessLicenseNumber = $accessLicenseNumber;

        return $this;
    }

    /**
     * @param string $name
     * @param string $shipperNumber
     * @param MarelloAddress $address
     * @return $this
     */
    public function setShipper($name, $shipperNumber, MarelloAddress $address)
    {
        $this->shipperName = $name;
        $this->shipperNumber = $shipperNumber;
        $this->shipperAddress = $address;

        return $this;
    }

    /**
     * @param string $name
     * @param MarelloAddress $address
     * @return $this
     */
    public function setShipFrom($name, MarelloAddress $address)
    {
        $this->shipFromName = $name;
        $this->shipFromAddress = $address;

        return $this;
    }

    /**
     * @param string $name
     * @param MarelloAddress $address
     * @return $this
     */
    public function setShipTo($name, MarelloAddress $address)
    {
        $this->shipToName = $name;
        $this->shipToAddress = $address;

        return $this;
    }

    /**
     * @param string $code
     * @param string $description
     * @return $this
     */
    public function setService($code, $description)
    {
        $this->serviceCode = $code;
        $this->serviceDescription = $description;

        return $this;
    }

    /**
     * @param Package $package
     */
    public function addPackage(Package $package)
    {
        $this->packages[] = $package;
    }

    /**
     * @param Package $package
     */
    public function removePackage(Package $package)
    {
        $this->packages = array_diff($this->packages, [$package]);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessLicenseNumber()
    {
        return $this->accessLicenseNumber;
    }

    /**
     * @param string $accessLicenseNumber
     * @return $this
     */
    public function setAccessLicenseNumber($accessLicenseNumber)
    {
        $this->accessLicenseNumber = $accessLicenseNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceDescription()
    {
        return $this->serviceDescription;
    }

    /**
     * @param string $serviceDescription
     * @return $this
     */
    public function setServiceDescription($serviceDescription)
    {
        $this->serviceDescription = $serviceDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceCode()
    {
        return $this->serviceCode;
    }

    /**
     * @param string $serviceCode
     * @return $this
     */
    public function setServiceCode($serviceCode)
    {
        $this->serviceCode = $serviceCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getShipperName()
    {
        return $this->shipperName;
    }

    /**
     * @param string $shipperName
     * @return $this
     */
    public function setShipperName($shipperName)
    {
        $this->shipperName = $shipperName;

        return $this;
    }

    /**
     * @return string
     */
    public function getShipperNumber()
    {
        return $this->shipperNumber;
    }

    /**
     * @param string $shipperNumber
     * @return $this
     */
    public function setShipperNumber($shipperNumber)
    {
        $this->shipperNumber = $shipperNumber;

        return $this;
    }

    /**
     * @return MarelloAddress
     */
    public function getShipperAddress()
    {
        return $this->shipperAddress;
    }

    /**
     * @param MarelloAddress $shipperAddress
     * @return $this
     */
    public function setShipperAddress(MarelloAddress $shipperAddress)
    {
        $this->shipperAddress = $shipperAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getShipFromName()
    {
        return $this->shipFromName;
    }

    /**
     * @param string $shipFromName
     * @return $this
     */
    public function setShipFromName($shipFromName)
    {
        $this->shipFromName = $shipFromName;

        return $this;
    }

    /**
     * @return MarelloAddress
     */
    public function getShipFromAddress()
    {
        return $this->shipFromAddress;
    }

    /**
     * @param MarelloAddress $shipFromAddress
     * @return $this
     */
    public function setShipFromAddress(MarelloAddress $shipFromAddress)
    {
        $this->shipFromAddress = $shipFromAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getShipToName()
    {
        return $this->shipToName;
    }

    /**
     * @param string $shipToName
     * @return $this
     */
    public function setShipToName($shipToName)
    {
        $this->shipToName = $shipToName;

        return $this;
    }

    /**
     * @return MarelloAddress
     */
    public function getShipToAddress()
    {
        return $this->shipToAddress;
    }

    /**
     * @param MarelloAddress $shipToAddress
     * @return $this
     */
    public function setShipToAddress(MarelloAddress $shipToAddress)
    {
        $this->shipToAddress = $shipToAddress;

        return $this;
    }

    /**
     * @return Package[]
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param Package[] $packages
     * @return $this
     */
    public function setPackages(array $packages)
    {
        $this->packages = $packages;

        return $this;
    }
}
