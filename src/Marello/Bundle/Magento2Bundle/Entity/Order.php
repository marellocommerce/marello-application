<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\Magento2Bundle\Model\ExtendOrder;
use Marello\Bundle\OrderBundle\Entity\Order as InnerOrder;
use Marello\Bundle\Magento2Bundle\Entity\Customer as MagentoCustomer;

/**
 * @todo Skip customer that has not email, first name, last name
 *
 * Keeps syncing states of order with remote Magento 2 instance by single channel
 *
 * @ORM\Table(
 *  name="marello_m2_order",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unq_order_channel_idx", columns={"channel_id", "origin_id"})
 *  }
 * )
 */
class Order extends ExtendOrder implements OriginAwareInterface, IntegrationAwareInterface
{
    use IntegrationEntityTrait, OriginTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Represents Marello Order that was created based on the data got from Magento
     *
     * @var InnerOrder
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\OrderBundle\Entity\Order")
     * @ORM\JoinColumn(name="inner_order_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $innerOrder;

    /**
     * @var MagentoCustomer
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\Magento2Bundle\Entity\Customer")
     * @ORM\JoinColumn(name="m2_customer_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $magentoCustomer;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return InnerOrder
     */
    public function getInnerOrder(): InnerOrder
    {
        return $this->innerOrder;
    }

    /**
     * @param InnerOrder $innerOrder
     * @return $this
     */
    public function setInnerOrder(InnerOrder $innerOrder): self
    {
        $this->innerOrder = $innerOrder;

        return $this;
    }

    /**
     * @return MagentoCustomer
     */
    public function getMagentoCustomer(): MagentoCustomer
    {
        return $this->magentoCustomer;
    }

    /**
     * @param MagentoCustomer $magentoCustomer
     * @return $this
     */
    public function setMagentoCustomer(MagentoCustomer $magentoCustomer): self
    {
        $this->magentoCustomer = $magentoCustomer;

        return $this;
    }

    /**
     * @param MagentoCustomer $magentoCustomer
     * @return bool
     */
    public function setMagentoCustomerAndFillInnerOrderWithCustomer(MagentoCustomer $magentoCustomer): bool
    {
        if (!$this->innerOrder || !$magentoCustomer->getInnerCustomer()) {
            return false;
        }

        $this->magentoCustomer = $magentoCustomer;

        $this->innerOrder->setCustomer(
            $magentoCustomer->getInnerCustomer()
        );

        return true;
    }
}
