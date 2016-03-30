<?php

namespace Marello\Bundle\PricingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * Represents a Marello ProductPrice
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\PricingBundle\Entity\Repository\ProductChannelPriceRepository")
 * @ORM\Table(
 *      name="marello_product_channel_price",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="marello_product_channel_price_uidx",
 *              columns={"product_id", "channel_id", "currency"}
 *          )
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Config(
 *  defaultValues={
 *      "entity"={"icon"="icon-usd"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class ProductChannelPrice extends BasePrice
{
    /**
     * @var SalesChannel
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $channel;

    /**
     * @return SalesChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param SalesChannel $channel
     *
     * @return $this
     */
    public function setChannel(SalesChannel $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
