<?php

namespace Marello\Bundle\ProductBundle\Twig;

use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamilyAwareInterface;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Manager\AttributeManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DynamicFieldsExtension extends AbstractExtension
{
    const NAME = 'marello_entity_config_fields';
    
    /**
     * @var AttributeManager
     */
    protected $attributeManager;

    /**
     * @param AttributeManager $attributeManager
     */
    public function __construct(AttributeManager $attributeManager)
    {
        $this->attributeManager = $attributeManager;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_entity_has_attribute',
                [$this, 'hasAttribute']
            )
        ];
    }

    /**
     * @param AttributeFamilyAwareInterface $entity
     * @param string $fieldName
     * @return bool
     */
    public function hasAttribute(AttributeFamilyAwareInterface $entity, $fieldName)
    {
        $groupsData = $this->attributeManager->getGroupsWithAttributes($entity->getAttributeFamily());
        foreach ($groupsData as $groupsDatum) {
            /** @var FieldConfigModel $attribute */
            foreach ($groupsDatum['attributes'] as $attribute) {
                if ($attribute) {
                    $field = $attribute->getFieldName();
                    if ($field === $fieldName) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}
