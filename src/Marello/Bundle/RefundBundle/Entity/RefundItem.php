<?php

namespace Marello\Bundle\RefundBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_refund_item")
 *
 * @Oro\Config
 */
class RefundItem implements CurrencyAwareInterface
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
    protected $name;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $quantity = 1;

    /**
     * @ORM\Column(type="money")
     *
     * @var int
     */
    protected $baseAmount = 0;

    /**
     * @ORM\Column(type="money")
     *
     * @var int
     */
    protected $refundAmount = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Refund", inversedBy="items")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var Refund
     */
    protected $refund;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\OrderItem")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var OrderItem
     */
    protected $orderItem;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * RefundItem constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @param $item
     *
     * @return RefundItem
     */
    public static function fromOrderItem(OrderItem $item)
    {
        $refund = new self();

        $refund
            ->setOrderItem($item)
            ->setName($item->getProductName())
            ->setBaseAmount($item->getPurchasePriceIncl());

        return $refund;
    }

    /**
     * @param ReturnItem $item
     *
     * @return RefundItem
     */
    public static function fromReturnItem(ReturnItem $item)
    {
        $refund = new self();

        $refund
            ->setOrderItem($item->getOrderItem())
            ->setName($item->getOrderItem()->getProductName())
            ->setBaseAmount($item->getOrderItem()->getPurchasePriceIncl())
            ->setRefundAmount($item->getOrderItem()->getPurchasePriceIncl() * $item->getQuantity())
            ->setQuantity($item->getQuantity());

        return $refund;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return RefundItem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return RefundItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getBaseAmount()
    {
        return $this->baseAmount;
    }

    /**
     * @param int $baseAmount
     *
     * @return RefundItem
     */
    public function setBaseAmount($baseAmount)
    {
        $this->baseAmount = $baseAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRefundAmount()
    {
        return $this->refundAmount;
    }

    /**
     * @param int $refundAmount
     *
     * @return RefundItem
     */
    public function setRefundAmount($refundAmount)
    {
        $this->refundAmount = $refundAmount;

        return $this;
    }

    /**
     * @return Refund
     */
    public function getRefund()
    {
        return $this->refund;
    }

    /**
     * @param Refund $refund
     *
     * @return RefundItem
     */
    public function setRefund($refund)
    {
        $this->refund = $refund;

        return $this;
    }

    /**
     * @return OrderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @param OrderItem $orderItem
     *
     * @return RefundItem
     */
    public function setOrderItem($orderItem)
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->getRefund()->getCurrency();
    }
}
