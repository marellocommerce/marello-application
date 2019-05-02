<?php

namespace Marello\Bundle\PricingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CoreBundle\Model\EntityCreatedUpdatedAtTrait;
use Marello\Bundle\PricingBundle\Model\CurrencyAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
class BasePrice implements CurrencyAwareInterface
{
    use EntityCreatedUpdatedAtTrait;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    protected $id;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="money")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $value;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=3)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true,
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $currency;

    /**
     * @var PriceType
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\PricingBundle\Entity\PriceType")
     * @ORM\JoinColumn(name="type", referencedColumnName="name", nullable=false, onDelete="CASCADE")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $type;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return PriceType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param PriceType $type
     * @return $this
     */
    public function setType(PriceType $type)
    {
        $this->type = $type;
        
        return $this;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }
}
