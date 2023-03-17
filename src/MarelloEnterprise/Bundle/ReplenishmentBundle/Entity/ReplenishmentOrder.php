<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertyAwareInterface;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Model\ExtendReplenishmentOrder;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;

/**
 * @ORM\Entity(
 *     repositoryClass="MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\Repository\ReplenishmentOrderRepository"
 * )
 * @ORM\Table(name="marello_repl_order")
 * @ORM\HasLifecycleCallbacks
 * @Oro\Config(
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class ReplenishmentOrder extends ExtendReplenishmentOrder implements
    DerivedPropertyAwareInterface,
    OrganizationAwareInterface
{
    use AuditableOrganizationAwareTrait;
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
     * @ORM\Column(name="repl_order_number", type="string", unique=true, nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var string
     */
    protected $replOrderNumber;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="origin_id", nullable=true, onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var Warehouse
     */
    protected $origin;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\InventoryBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="destination_id", nullable=true, onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var Warehouse
     */
    protected $destination;

    /**
     * @ORM\Column(name="execution_date_time", type="datetime", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var \DateTime
     */
    protected $executionDateTime;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var string
     */
    protected $description;

    /**
     * @var Collection|ReplenishmentOrderItem[]
     *
     * @ORM\OneToMany(targetEntity="ReplenishmentOrderItem", mappedBy="order", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "email"={
     *              "available_in_template"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $replOrderItems;

    /**
     * @var string
     *
     * @ORM\Column(name="ro_code", type="string", nullable=true, unique=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $roCode;

    /**
     * @ORM\ManyToOne(targetEntity="ReplenishmentOrderConfig")
     * @ORM\JoinColumn(name="repl_order_config_id", nullable=true, onDelete="SET NULL")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var ReplenishmentOrderConfig
     */
    protected $replOrderConfig;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->replOrderItems = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setDerivedProperty($id)
    {
        if (!$this->replOrderNumber) {
            $this->setReplOrderNumber(sprintf('%09d', $id));
        }
    }

    /**
     * @return string
     */
    public function getReplOrderNumber()
    {
        return $this->replOrderNumber;
    }

    /**
     * @param string $replOrderNumber
     * @return ReplenishmentOrder
     */
    public function setReplOrderNumber($replOrderNumber)
    {
        $this->replOrderNumber = $replOrderNumber;

        return $this;
    }

    /**
     * @return Warehouse
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param Warehouse $origin
     * @return ReplenishmentOrder
     */
    public function setOrigin(Warehouse $origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return Warehouse
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param Warehouse $destination
     * @return ReplenishmentOrder
     */
    public function setDestination(Warehouse $destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExecutionDateTime()
    {
        return $this->executionDateTime;
    }

    /**
     * @param \DateTime $executionDateTime
     * @return ReplenishmentOrder
     */
    public function setExecutionDateTime(\DateTime $executionDateTime)
    {
        $this->executionDateTime = $executionDateTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ReplenishmentOrder
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|ReplenishmentOrderItem[]
     */
    public function getReplOrderItems()
    {
        return $this->replOrderItems;
    }

    /**
     * @param Collection|ReplenishmentOrderItem[] $replOrderItems
     * @return ReplenishmentOrder
     */
    public function setReplOrderItems(Collection $replOrderItems)
    {
        $this->replOrderItems = $replOrderItems;

        return $this;
    }

    /**
     * @param ReplenishmentOrderItem $item
     *
     * @return $this
     */
    public function addReplOrderItem(ReplenishmentOrderItem $item)
    {
        if (!$this->replOrderItems->contains($item)) {
            $this->replOrderItems->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    /**
     * @param ReplenishmentOrderItem $item
     *
     * @return $this
     */
    public function removeReplOrderItem(ReplenishmentOrderItem $item)
    {
        if ($this->replOrderItems->contains($item)) {
            $this->replOrderItems->removeElement($item);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRoCode()
    {
        return $this->roCode;
    }

    /**
     * @param string $roCode
     * @return ReplenishmentOrder
     */
    public function setRoCode($roCode)
    {
        $this->roCode = $roCode;

        return $this;
    }

    /**
     * @return ReplenishmentOrderConfig
     */
    public function getReplOrderConfig()
    {
        return $this->replOrderConfig;
    }

    /**
     * @param ReplenishmentOrderConfig $replOrderConfig
     * @return ReplenishmentOrder
     */
    public function setReplOrderConfig(ReplenishmentOrderConfig $replOrderConfig)
    {
        $this->replOrderConfig = $replOrderConfig;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('#%s', $this->replOrderNumber);
    }
}
