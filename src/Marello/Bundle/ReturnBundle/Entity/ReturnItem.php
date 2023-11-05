<?php

namespace Marello\Bundle\ReturnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\InventoryBundle\Model\InventoryItemAwareInterface;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\ReturnBundle\Entity\Repository\ReturnItemRepository")
 * @ORM\Table(name="marello_return_item")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Config(
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id"
 *          }
 *      }
 * )
 */
class ReturnItem implements
    CurrencyAwareInterface,
    InventoryItemAwareInterface,
    OrganizationAwareInterface,
    ExtendEntityInterface
{
    use EntityCreatedUpdatedAtTrait;
    use AuditableOrganizationAwareTrait;
    use ExtendEntityTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ReturnEntity
     *
     * @ORM\ManyToOne(targetEntity="ReturnEntity", inversedBy="returnItems")
     * @ORM\JoinColumn(name="return_id", onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $return;

    /**
     * @var OrderItem
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\OrderItem", inversedBy="returnItems")
     * @ORM\JoinColumn(name="order_item_id")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $orderItem;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $quantity;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var string
     */
    protected $status;

    /**
     * ReturnItem constructor.
     *
     * @param OrderItem $orderItem
     */
    public function __construct(OrderItem $orderItem = null)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * @return InventoryItem|null
     */
    public function getInventoryItem()
    {
        return $this->getOrderItem()->getInventoryItem();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ReturnEntity
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @param ReturnEntity $return
     *
     * @return $this
     */
    public function setReturn($return)
    {
        $this->return = $return;

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
     * @return $this
     */
    public function setOrderItem($orderItem)
    {
        $this->orderItem = $orderItem;

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
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get currency for returnItem from "sibling" orderItem
     */
    public function getCurrency()
    {
        return $this->orderItem->getCurrency();
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     *
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
