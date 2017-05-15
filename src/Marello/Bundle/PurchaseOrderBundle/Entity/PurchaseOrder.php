<?php

namespace Marello\Bundle\PurchaseOrderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertyAwareInterface;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="marello_purchase_order")
 * @Oro\Config(
 *      routeView="marello_purchaseorder_purchaseorder_view",
 *      routeName="marello_purchaseorder_purchaseorder_index",
 *      routeCreate="marello_purchaseorder_purchaseorder_create",
 *      defaultValues={
 *          "ownership"={
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          }
 *      }
 * )
 */
class PurchaseOrder implements DerivedPropertyAwareInterface
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
     * @ORM\Column(name="purchase_order_number", type="string", nullable=true)
     *
     * @var string
     */
    protected $purchaseOrderNumber;

    /**
     * @var Collection|PurchaseOrderItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="PurchaseOrderItem",
     *     mappedBy="order",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @Oro\ConfigField(
     *      defaultValues={
     *          "email"={
     *              "available_in_template"=true
     *          }
     *      }
     * )
     */
    protected $items;


    /**
     * @var Supplier
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SupplierBundle\Entity\Supplier")
     * @ORM\JoinColumn(name="supplier_id", nullable=false)
     */
    protected $supplier;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", nullable=false)
     */
    protected $organization;

    /**
     * Creates order using products
     *
     * @param array|Product[] $products
     * @param Organization    $organization
     *
     * @return self
     */
    public static function usingProducts(array $products, Organization $organization)
    {
        $order = new self($organization);

        foreach ($products as $product) {
            $virtualStock = array_reduce(
                $product->getInventoryItems()->toArray(),
                function ($carry, InventoryItem $item) {
                    return $carry + $item->getVirtualStock();
                },
                0
            );

            $amount = $product->getDesiredStockLevel() - $virtualStock;
            $order->addItem(
                new PurchaseOrderItem($product, $amount)
            );
        }

        return $order;
    }


    /**
     * PurchaseOrder constructor.
     *
     * @param Organization $organization
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
        $this->items = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPurchaseOrderNumber()
    {
        return $this->purchaseOrderNumber;
    }

    /**
     * @return Collection|PurchaseOrderItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $purchaseOrderNumber
     *
     * @return $this
     */
    public function setPurchaseOrderNumber($purchaseOrderNumber)
    {
        $this->purchaseOrderNumber = $purchaseOrderNumber;

        return $this;
    }

    /**
     * @param PurchaseOrderItem $item
     *
     * @return $this
     */
    public function addItem(PurchaseOrderItem $item)
    {
        $this->items->add($item->setOrder($this));

        return $this;
    }

    /**
     * @param PurchaseOrderItem $item
     *
     * @return $this
     */
    public function removeItem(PurchaseOrderItem $item)
    {
        $this->items->removeElement($item);

        return $this;
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param Supplier $supplier
     *
     * @return $this
     */
    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     *
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @param $id
     */
    public function setDerivedProperty($id)
    {
        if (!$this->purchaseOrderNumber) {
            $this->setPurchaseOrderNumber(sprintf('%09d', $id));
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('#%s', $this->purchaseOrderNumber);
    }
}
