<?php

namespace Marello\Bundle\PurchaseOrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\PurchaseOrderBundle\Entity\Repository\PurchaseOrderItemRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="marello_purchase_order_item")
 */
class PurchaseOrderItem
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
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product")
     */
    protected $product;

    /**
     * @var PurchaseOrder
     *
     * @ORM\ManyToOne(targetEntity="PurchaseOrder", inversedBy="items")
     * @ORM\JoinColumn
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $productSku;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $productName;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $orderedAmount;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $receivedAmount = 0;

    /**
     * @var array $data
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     */
    protected $data;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $status = 'pending';

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
     * PurchaseOrderItem constructor.
     *
     * @param Product $product
     * @param int $orderedAmount
     */
    public function __construct(Product $product, $orderedAmount)
    {
        $this->product = $product;
        $this->orderedAmount = $orderedAmount;
        $this->createdAt = new \DateTime();
        $this->productName = $this->product->getName();
        $this->productSku = $this->product->getSku();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @Assert\Callback
     *
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (($this->receivedAmount < 0) || ($this->receivedAmount > $this->orderedAmount)) {
            $context
                ->buildViolation('marello.purchase_order.purchaseorderitem.validation.received_amount')
                ->atPath('receivedAmount')
                ->addViolation();
        }
    }

    /**
     * @param PurchaseOrder $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderedAmount()
    {
        return $this->orderedAmount;
    }

    /**
     * @param int $orderedAmount
     *
     * @return $this
     */
    public function setOrderedAmount($orderedAmount)
    {
        $this->orderedAmount = $orderedAmount;

        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getProductSku()
    {
        return $this->productSku;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @return int
     */
    public function getReceivedAmount()
    {
        return $this->receivedAmount;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $receivedAmount
     */
    public function setReceivedAmount($receivedAmount)
    {
        $this->receivedAmount = $receivedAmount;
    }
}
