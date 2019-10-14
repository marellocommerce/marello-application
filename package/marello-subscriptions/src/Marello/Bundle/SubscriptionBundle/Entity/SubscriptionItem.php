<?php

namespace Marello\Bundle\SubscriptionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as JMS;

use Oro\Bundle\CurrencyBundle\Entity\PriceAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\OrganizationBundle\Entity\Ownership\AuditableOrganizationAwareTrait;

use Marello\Bundle\SubscriptionBundle\Model\ExtendSubscriptionItem;

/**
 * @ORM\Entity()
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
 * @ORM\Table(name="marello_subscription_item")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("ALL")
 */
class SubscriptionItem extends ExtendSubscriptionItem implements PriceAwareInterface, OrganizationAwareInterface
{
    use AuditableOrganizationAwareTrait;
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JMS\Expose
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sku",type="string", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $sku;

    /**
     * @var string
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $duration;

    /**
     * @var float
     *
     * @ORM\Column(name="price",type="money")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @JMS\Expose
     */
    protected $price;

    /**
     * @var float
     *
     * @ORM\Column(name="special_price",type="money", nullable=true)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     *
     * @JMS\Expose
     */
    protected $specialPrice;
    
    /**
     * @var string
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $specialPriceDuration;

    /**
     * @var Subscription
     *
     * @ORM\OneToOne(targetEntity="Subscription", mappedBy="item")
     */
    protected $subscription;

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
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     * @return SubscriptionItem
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     * @return SubscriptionItem
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return SubscriptionItem
     */
    public function setPrice($price)
    {
        $this->price = $price;
        
        return $this;
    }

    /**
     * @return float
     */
    public function getSpecialPrice()
    {
        return $this->specialPrice;
    }

    /**
     * @param float $specialPrice
     * @return SubscriptionItem
     */
    public function setSpecialPrice($specialPrice)
    {
        $this->specialPrice = $specialPrice;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getSpecialPriceDuration()
    {
        return $this->specialPriceDuration;
    }

    /**
     * @param string $specialPriceDuration
     * @return SubscriptionItem
     */
    public function setSpecialPriceDuration($specialPriceDuration)
    {
        $this->specialPriceDuration = $specialPriceDuration;
        
        return $this;
    }

    /**
     * Get currency for SubscriptionItem from Subscription
     */
    public function getCurrency()
    {
        return $this->subscription->getCurrency();
    }

    /**
     * @return Subscription
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * @param Subscription $subscription
     * @return SubscriptionItem
     */
    public function setSubscription(Subscription $subscription)
    {
        $this->subscription = $subscription;
        
        return $this;
    }
}
