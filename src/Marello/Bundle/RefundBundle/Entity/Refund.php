<?php

namespace Marello\Bundle\RefundBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Marello\Bundle\CoreBundle\DerivedProperty\DerivedPropertyAwareInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="marello_refund")
 *
 * @Oro\Config
 */
class Refund implements DerivedPropertyAwareInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     *
     * @var string
     */
    protected $refundNumber;

    /**
     * @ORM\Column(type="money")
     *
     * @var int
     */
    protected $refundAmount;

    /**
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Customer")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Customer
     */
    protected $customer;

    /**
     * @ORM\OneToMany(targetEntity="RefundItem", mappedBy="refund", cascade={"persist"}, orphanRemoval=true)
     *
     * @var Collection|RefundItem[]
     */
    protected $items;

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
     * Refund constructor.
     */
    public function __construct()
    {
        $this->updatedAt = new \DateTime();
        $this->items = new ArrayCollection();
    }

    /**
     * @param int $id
     */
    public function setDerivedProperty($id)
    {
        if (!$this->refundNumber) {
            $this->setRefundNumber(sprintf('%09d', $id));
        }
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
    public function getRefundNumber()
    {
        return $this->refundNumber;
    }

    /**
     * @param string $refundNumber
     *
     * @return Refund
     */
    public function setRefundNumber($refundNumber)
    {
        $this->refundNumber = $refundNumber;

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
     * @return Refund
     */
    public function setRefundAmount($refundAmount)
    {
        $this->refundAmount = $refundAmount;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     *
     * @return Refund
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param RefundItem $item
     *
     * @return $this
     */
    public function addItem(RefundItem $item)
    {
        $this->items->add($item->setRefund($this));

        return $this;
    }

    /**
     * @param RefundItem $item
     *
     * @return $this
     */
    public function removeItem(RefundItem $item)
    {
        $this->items->removeElement($item);

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
}
