<?php

namespace Marello\Bundle\PurchaseOrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\InventoryBundle\Entity\InventoryItemAwareInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\PurchaseOrderBundle\Entity\Repository\PurchaseOrderItemRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="marello_purchase_order_item")
 * @Oro\Config()
 */
class PurchaseOrderItem implements
    InventoryItemAwareInterface
{
    use EntityCreatedUpdatedAtTrait;
    
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
     * @ORM\Column(name="product_sku", type="string")
     */
    protected $productSku;

    /**
     * @var string
     *
     * @ORM\Column(name="product_name", type="string")
     */
    protected $productName;

    /**
     * @var string
     *
     * @ORM\Column(name="supplier", type="string")
     */
    protected $supplier;

    /**
     * @var int
     *
     * @ORM\Column(name="ordered_amount", type="integer")
     */
    protected $orderedAmount;

    /**
     * @var int
     *
     * @ORM\Column(name="received_amount", type="integer")
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
     * @ORM\Column(name="status", type="string")
     */
    protected $status = 'pending';

    /**
     * PurchaseOrderItem constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('#%s', $this->productName);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Marello\Bundle\InventoryBundle\Entity\InventoryItem[]
     */
    public function getInventoryItems()
    {
        return $this->getProduct()->getInventoryItems();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
                ->buildViolation('marello.purchaseorder.purchaseorderitem.validation.received_amount')
                ->atPath('receivedAmount')
                ->addViolation();
        }
    }

    /**
     * @param PurchaseOrder $order
     *
     * @return $this
     */
    public function setOrder(PurchaseOrder $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return PurchaseOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        $this->productName = $this->product->getName();
        $this->productSku = $this->product->getSku();

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
     * @return int
     */
    public function getOrderedAmount()
    {
        return $this->orderedAmount;
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
     * @param String $supplier
     *
     * @return string
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @return string
     */
    public function getSupplier()
    {
        return $this->supplier;
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
     * @param int $receivedAmount
     */
    public function setReceivedAmount($receivedAmount)
    {
        $this->receivedAmount = $receivedAmount;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function preSaveSetSupplier()
    {
        $this->setSupplier($this->order->getSupplier()->getName());
    }
}
