<?php

namespace Marello\Bundle\ReturnBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ReturnBundle\Model\ExtendReturnEntity;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

/**
 * @ORM\Entity()
 * @ORM\Table(name="marello_return_return")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Config()
 */
class ReturnEntity extends ExtendReturnEntity
{

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Order")
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $returnNumber;

    /**
     * @var Collection|ReturnItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\ReturnBundle\Entity\ReturnItem",
     *     mappedBy="return",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn
     */
    protected $returnItems;

    /**
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
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
     * @ORM\Column(type="datetime")
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
     * ReturnEntity constructor.
     */
    public function __construct()
    {
        $this->returnItems = new ArrayCollection();
    }

    /**
     * Copies product sku and name to attributes within this return item.
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * @return string
     */
    public function getReturnNumber()
    {
        return $this->returnNumber;
    }

    /**
     * @param string $returnNumber
     *
     * @return $this
     */
    public function setReturnNumber($returnNumber)
    {
        $this->returnNumber = $returnNumber;

        return $this;
    }

    /**
     * @return Collection|ReturnItem[]
     */
    public function getReturnItems()
    {
        return $this->returnItems;
    }

    /**
     * @param ReturnItem $item
     *
     * @return $this
     */
    public function addReturnItem(ReturnItem $item)
    {
        $this->returnItems->add($item->setReturn($this));

        return $this;
    }

    /**
     * @param ReturnItem $item
     *
     * @return $this
     */
    public function removeReturnItem(ReturnItem $item)
    {
        $this->returnItems->removeElement($item);

        return $this;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }

    /**
     * @param WorkflowItem $workflowItem
     *
     * @return $this
     */
    public function setWorkflowItem($workflowItem)
    {
        $this->workflowItem = $workflowItem;

        return $this;
    }

    /**
     * @return WorkflowStep
     */
    public function getWorkflowStep()
    {
        return $this->workflowStep;
    }

    /**
     * @param WorkflowStep $workflowStep
     *
     * @return $this
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->workflowStep = $workflowStep;

        return $this;
    }
}
