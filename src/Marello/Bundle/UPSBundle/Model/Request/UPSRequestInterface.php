<?php

namespace Marello\Bundle\UPSBundle\Model\Request;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\UPSBundle\Model\Package;

interface UPSRequestInterface
{
    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return string
     */
    public function stringify();
    
    /**
     * @param $username
     * @param $password
     * @param $accessLicenseNumber
     * @return $this
     */
    public function setSecurity($username, $password, $accessLicenseNumber);

    /**
     * @param string $name
     * @param string $shipperNumber
     * @param MarelloAddress $address
     * @return $this
     */
    public function setShipper($name, $shipperNumber, MarelloAddress $address);

    /**
     * @param string $name
     * @param MarelloAddress $address
     * @return $this
     */
    public function setShipFrom($name, MarelloAddress $address);

    /**
     * @param string $name
     * @param MarelloAddress $address
     * @return $this
     */
    public function setShipTo($name, MarelloAddress $address);

    /**
     * @param string $code
     * @param string $description
     * @return $this
     */
    public function setService($code, $description);

    /**
     * @param Package $package
     */
    public function addPackage(Package $package);

    /**
     * @param Package $package
     */
    public function removePackage(Package $package);

    /**
     * @return string
     */
    public function getUsername();

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username);

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password);

    /**
     * @return string
     */
    public function getAccessLicenseNumber();

    /**
     * @param string $accessLicenseNumber
     * @return $this
     */
    public function setAccessLicenseNumber($accessLicenseNumber);

    /**
     * @return string
     */
    public function getServiceDescription();

    /**
     * @param string $serviceDescription
     * @return $this
     */
    public function setServiceDescription($serviceDescription);

    /**
     * @return string
     */
    public function getServiceCode();

    /**
     * @param string $serviceCode
     * @return $this
     */
    public function setServiceCode($serviceCode);

    /**
     * @return string
     */
    public function getShipperName();

    /**
     * @param string $shipperName
     * @return $this
     */
    public function setShipperName($shipperName);

    /**
     * @return string
     */
    public function getShipperNumber();

    /**
     * @param string $shipperNumber
     * @return $this
     */
    public function setShipperNumber($shipperNumber);

    /**
     * @return MarelloAddress
     */
    public function getShipperAddress();

    /**
     * @param MarelloAddress $shipperAddress
     * @return $this
     */
    public function setShipperAddress(MarelloAddress $shipperAddress);

    /**
     * @return string
     */
    public function getShipFromName();

    /**
     * @param string $shipFromName
     * @return $this
     */
    public function setShipFromName($shipFromName);

    /**
     * @return MarelloAddress
     */
    public function getShipFromAddress();

    /**
     * @param MarelloAddress $shipFromAddress
     * @return $this
     */
    public function setShipFromAddress(MarelloAddress $shipFromAddress);

    /**
     * @return string
     */
    public function getShipToName();

    /**
     * @param string $shipToName
     * @return $this
     */
    public function setShipToName($shipToName);

    /**
     * @return MarelloAddress
     */
    public function getShipToAddress();

    /**
     * @param MarelloAddress $shipToAddress
     * @return $this
     */
    public function setShipToAddress(MarelloAddress $shipToAddress);

    /**
     * @return Package[]
     */
    public function getPackages();

    /**
     * @param Package[] $packages
     * @return $this
     */
    public function setPackages(array $packages);
}
