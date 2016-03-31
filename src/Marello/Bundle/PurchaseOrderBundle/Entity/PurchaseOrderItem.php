<?php

namespace Marello\Bundle\PurchaseOrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity
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
    protected $receivedAmount;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $status;

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
     * @ORM\Column(name="updated_at", type="datetime")
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
     * @param Product       $product
     * @param int           $orderedAmount
     */
    public function __construct(Product $product, $orderedAmount)
    {
        $this->product       = $product;
        $this->orderedAmount = $orderedAmount;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt   = new \DateTime();
        $this->productName = $this->product->getName();
        $this->productSku  = $this->product->getSku();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
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
}
