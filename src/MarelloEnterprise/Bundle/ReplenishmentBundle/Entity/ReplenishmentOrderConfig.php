<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Model\ExtendReplenishmentOrderConfig;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="marello_repl_order_config")
 * @ORM\HasLifecycleCallbacks
 * @Oro\Config(
 *      defaultValues={
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class ReplenishmentOrderConfig extends ExtendReplenishmentOrderConfig implements OrganizationAwareInterface
{
    use AuditableOrganizationAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="origins", nullable=true, type="json_array")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var array
     */
    protected $origins = [];

    /**
     * @ORM\Column(name="destinations", nullable=true, type="json_array")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var array
     */
    protected $destinations = [];

    /**
     * @ORM\Column(name="products", type="json_array")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var array
     */
    protected $products = [];

    /**
     * @ORM\OneToMany(targetEntity="ReplenishmentOrderManualItemConfig", mappedBy="orderConfig", cascade={"all"})
     *
     * @var Collection|ReplenishmentOrderManualItemConfig[]
     */
    protected $manualItems;

    /**
     * @ORM\Column(name="strategy", type="string", nullable=false, length=50)
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
    protected $strategy;

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
     * @ORM\Column(name="percentage", type="float")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @var float
     */
    protected $percentage;
    
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

    public function __construct()
    {
        parent::__construct();
        $this->manualItems = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getOrigins()
    {
        return $this->origins;
    }

    /**
     * @param array $origins
     * @return ReplenishmentOrderConfig
     */
    public function setOrigins($origins)
    {
        $this->origins = $origins;
        
        return $this;
    }

    /**
     * @return array
     */
    public function getDestinations()
    {
        return $this->destinations;
    }

    /**
     * @param array $destinations
     * @return ReplenishmentOrderConfig
     */
    public function setDestinations($destinations)
    {
        $this->destinations = $destinations;

        return $this;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param array $products
     * @return ReplenishmentOrderConfig
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @param string $strategy
     * @return ReplenishmentOrderConfig
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;

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
     * @return ReplenishmentOrderConfig
     */
    public function setExecutionDateTime(\DateTime $executionDateTime = null)
    {
        $this->executionDateTime = $executionDateTime;

        return $this;
    }
    
    /**
     * @return float
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * @param float $percentage
     * @return ReplenishmentOrderConfig
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

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
     * @return Collection|ReplenishmentOrderManualItemConfig[]
     */
    public function getManualItems(): Collection
    {
        return $this->manualItems;
    }

    /**
     * @param ReplenishmentOrderManualItemConfig $manualItem
     * @return self
     */
    public function addManualItem(ReplenishmentOrderManualItemConfig $manualItem): self
    {
        if (!$this->manualItems->contains($manualItem)) {
            $manualItem->setOrderConfig($this);
            $this->manualItems->add($manualItem);
        }

        return $this;
    }

    /**
     * @param ReplenishmentOrderManualItemConfig $manualItem
     * @return self
     */
    public function removeManualItem(ReplenishmentOrderManualItemConfig $manualItem): self
    {
        if ($this->manualItems->contains($manualItem)) {
            $this->manualItems->remove($manualItem);
        }

        return $this;
    }
}
