<?php

namespace Marello\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Model\ExtendShipment;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_shipment")
 * @Oro\Config
 */
class Shipment extends ExtendShipment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $shippingService;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $upsShipmentDigest;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $identificationNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $upsPackageTrackingNumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $base64EncodedLabel;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getShippingService()
    {
        return $this->shippingService;
    }

    /**
     * @param string $shippingService
     *
     * @return $this
     */
    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpsShipmentDigest()
    {
        return $this->upsShipmentDigest;
    }

    /**
     * @param string $upsShipmentDigest
     *
     * @return $this
     */
    public function setUpsShipmentDigest($upsShipmentDigest)
    {
        $this->upsShipmentDigest = $upsShipmentDigest;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentificationNumber()
    {
        return $this->identificationNumber;
    }

    /**
     * @param string $identificationNumber
     *
     * @return $this
     */
    public function setIdentificationNumber($identificationNumber)
    {
        $this->identificationNumber = $identificationNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpsPackageTrackingNumber()
    {
        return $this->upsPackageTrackingNumber;
    }

    /**
     * @param string $upsPackageTrackingNumber
     *
     * @return $this
     */
    public function setUpsPackageTrackingNumber($upsPackageTrackingNumber)
    {
        $this->upsPackageTrackingNumber = $upsPackageTrackingNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getBase64EncodedLabel()
    {
        return $this->base64EncodedLabel;
    }

    /**
     * @param string $base64EncodedLabel
     *
     * @return $this
     */
    public function setBase64EncodedLabel($base64EncodedLabel)
    {
        $this->base64EncodedLabel = $base64EncodedLabel;

        return $this;
    }
}
