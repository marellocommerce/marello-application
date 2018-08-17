<?php

namespace Marello\Bundle\PricingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity()
 * @Oro\Config(
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 * @ORM\Table(name="marello_pricing_price_type")
 **/
class PriceType
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $label;

    /**
     * @param string $name
     * @param string $label
     */
    public function __construct($name, $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }
}
