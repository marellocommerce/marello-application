<?php

namespace Marello\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\OrderBundle\Entity\Order;

/**
 * @ORM\Entity
 */
class Shipment
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
     * @ORM\OneToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Order", inversedBy="shipment")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var Order
     */
    protected $order;

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
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }
}
