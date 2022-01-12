<?php

namespace Marello\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * Class WarehouseType
 * @package Marello\Bundle\SalesBundle\Entity
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelTypeRepository")
 * @ORM\Table(name="marello_sales_channel_type")
 * @Oro\Config(
 *      defaultValues={
 *          "grouping"={
 *              "groups"={"dictionary"}
 *          }
 *      }
 * )
 */
class SalesChannelType
{
    /**
     * @ORM\Column(name="name", type="string", length=64)
     * @ORM\Id
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $name;

    /**
     * @ORM\Column(name="label", type="string", length=255, unique=true)
     */
    protected $label;

    /**
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * Get type name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get type name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set warehouse type label
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get service type label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->label;
    }
}
