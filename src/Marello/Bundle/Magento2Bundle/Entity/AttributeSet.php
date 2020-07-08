<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\Magento2Bundle\Model\ExtendAttributeSet;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="marello_m2_attributeset",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unq_attributeset_idx", columns={"channel_id", "origin_id"})
 *  }
 * )
 * @Config()
 */
class AttributeSet extends ExtendAttributeSet implements OriginAwareInterface, IntegrationAwareInterface
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
     * @var string
     *
     * @ORM\Column(name="attribute_set_name", type="string", length=255, nullable=false)
     */
    protected $attributeSetName;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $attributeSetName
     *
     * @return AttributeSet
     */
    public function setAttributeSetName(string $attributeSetName = null): self
    {
        $this->attributeSetName = $attributeSetName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttributeSetName(): ?string
    {
        return $this->attributeSetName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getAttributeSetName();
    }
}
