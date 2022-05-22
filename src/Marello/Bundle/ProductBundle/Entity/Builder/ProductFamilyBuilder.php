<?php

namespace Marello\Bundle\ProductBundle\Entity\Builder;

use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\EntityConfigBundle\Manager\AttributeGroupManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provide functionality to create Product Families
 */
class ProductFamilyBuilder
{
    const DEFAULT_FAMILY_CODE = 'marello_default';

    /** @var array */
    private static $groups = [
        [
            'groupLabel' => 'General',
            'groupCode' => 'general',
            'attributes' => [
                'sku',
                'names',
                'channels',
                'status',
                'prices',
                'channelPrices',
                'taxCode',
                'salesChannelTaxCodes',
                'weight',
                'manufacturingCode',
                'warranty',
                'suppliers',
                'categories',
                'image',
            ],
            'groupVisibility' => true,
        ]
    ];

    /** @var TranslatorInterface */
    private $translator;

    /** @var AttributeGroupManager */
    private $attributeGroupManager;

    /** @var AttributeFamily */
    private $family;

    /**
     * @param TranslatorInterface $translator
     * @param AttributeGroupManager $attributeGroupManager
     */
    public function __construct(TranslatorInterface $translator, AttributeGroupManager $attributeGroupManager)
    {
        $this->translator = $translator;
        $this->attributeGroupManager = $attributeGroupManager;
    }

    /**
     * @param Organization $organization
     * @return $this
     */
    public function createDefaultFamily(Organization $organization)
    {
        $this->family = new AttributeFamily();
        $this->family->setCode(self::DEFAULT_FAMILY_CODE);
        $this->family->setEntityClass(Product::class);
        $this->family->setOwner($organization);
        $this->family->setDefaultLabel(
            $this->translator->trans('oro.entityconfig.attribute.entity.attributefamily.default.label')
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addDefaultAttributeGroups()
    {
        if (!$this->family instanceof AttributeFamily) {
            throw new \LogicException(
                sprintf('Attribute groups can only be added to an instance of %s.', AttributeFamily::class)
            );
        }
        $attributeGroups = $this->attributeGroupManager->createGroupsWithAttributes(
            Product::class,
            self::$groups
        );
        foreach ($attributeGroups as $attributeGroup) {
            $this->family->addAttributeGroup($attributeGroup);
        }

        return $this;
    }

    /**
     * @return AttributeFamily|null
     */
    public function getFamily(): ?AttributeFamily
    {
        return $this->family;
    }
}
