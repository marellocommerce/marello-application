<?php

namespace Marello\Bundle\ProductBundle\EventListener;

use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamilyAwareInterface;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroup;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Manager\AttributeManager;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;

class AttributeFormViewListener
{
    /**
     * @var array
     */
    private $fieldsRestrictedToMove = [
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
        'barcode'
    ];
    /**
     * @var AttributeManager
     */
    private $attributeManager;

    /**
     * @param AttributeManager $attributeManager
     */
    public function __construct(AttributeManager $attributeManager)
    {
        $this->attributeManager = $attributeManager;
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onEdit(BeforeListRenderEvent $event)
    {
        $entity = $event->getEntity();

        if (!$entity instanceof AttributeFamilyAwareInterface) {
            return;
        }

        $scrollData = $event->getScrollData();
        $formView = $event->getFormView();
        $groupsData = $this->attributeManager->getGroupsWithAttributes($entity->getAttributeFamily());
        $this->filterGroupAttributes($groupsData, 'form', 'is_enabled');
        $this->addNotEmptyGroupBlocks($scrollData, $groupsData);

        foreach ($groupsData as $groupsDatum) {
            /** @var AttributeGroup $group */
            $group = $groupsDatum['group'];
            /** @var FieldConfigModel $attribute */
            foreach ($groupsDatum['attributes'] as $attribute) {
                $fieldId = $attribute->getFieldName();
                if (in_array($fieldId, $this->getRestrictedToMoveFields(), true)) {
                    continue;
                }
                $attributeView = $formView->offsetGet($fieldId);

                if (!$attributeView->isRendered()) {
                    $html = $event->getEnvironment()->render('@OroEntityConfig/Attribute/row.html.twig', [
                        'child' => $attributeView,
                    ]);

                    $subblockId = $scrollData->addSubBlock($group->getCode());
                    $scrollData->addSubBlockData($group->getCode(), $subblockId, $html, $fieldId);
                } else {
                    $this->moveFieldToBlock($scrollData, $attribute->getFieldName(), $group->getCode());
                }
            }
        }

        $this->combineGroupBlocks($scrollData);
        $this->removeEmptyGroupBlocks($scrollData);
    }

    /**
     * @param ScrollData $scrollData
     */
    private function combineGroupBlocks(ScrollData $scrollData)
    {
        $data = $scrollData->getData();
        if (empty($data[ScrollData::DATA_BLOCKS])) {
            return;
        }
        $notAttributesGroupBlocksByIds = [];
        $notAttributesGroupBlocksByTitles = [];
        foreach ($data[ScrollData::DATA_BLOCKS] as $blockId => $blockData) {
            if (!is_string($blockId)) {
                $notAttributesGroupBlocksByIds[$blockId] = $blockData;
                $notAttributesGroupBlocksByTitles[$blockData[ScrollData::TITLE]] = $blockId;
            }
        }
        foreach ($data[ScrollData::DATA_BLOCKS] as $blockId => $data) {
            if (!is_string($blockId)) {
                continue;
            }
            $isEmpty = true;
            if (!empty($data[ScrollData::SUB_BLOCKS])) {
                if (isset($notAttributesGroupBlocksByTitles[$data[ScrollData::TITLE]])) {
                    foreach ($data[ScrollData::SUB_BLOCKS] as $subblockId => $subblockData) {
                        if (!empty($subblockData[ScrollData::DATA])) {
                            foreach ($subblockData[ScrollData::DATA] as $fieldName => $fieldData) {
                                $this->moveFieldToBlock(
                                    $scrollData,
                                    $fieldName,
                                    $notAttributesGroupBlocksByTitles[$data[ScrollData::TITLE]]
                                );
                            }
                        }
                    }
                } else {
                    $isEmpty = false;
                }
            }

            if ($isEmpty) {
                $scrollData->removeNamedBlock($blockId);
            }
        }
    }

    /**
     * @param ScrollData $scrollData
     */
    private function removeEmptyGroupBlocks(ScrollData $scrollData)
    {
        $data = $scrollData->getData();
        if (empty($data[ScrollData::DATA_BLOCKS])) {
            return;
        }

        foreach ($data[ScrollData::DATA_BLOCKS] as $blockId => $data) {
            if (!is_string($blockId)) {
                continue;
            }
            $isEmpty = true;
            if (!empty($data[ScrollData::SUB_BLOCKS])) {
                foreach ($data[ScrollData::SUB_BLOCKS] as $subblockId => $subblockData) {
                    if (!empty($subblockData[ScrollData::DATA])) {
                        $isEmpty = false;
                    }
                }
            }

            if ($isEmpty) {
                $scrollData->removeNamedBlock($blockId);
            }
        }
    }

    /**
     * @param ScrollData $scrollData
     * @param array $groups
     */
    private function addNotEmptyGroupBlocks(ScrollData $scrollData, array $groups)
    {
        foreach ($groups as $group) {
            if (!empty($group['attributes'])) {
                /** @var AttributeGroup $currentGroup */
                $currentGroup = $group['group'];
                $scrollData->addNamedBlock($currentGroup->getCode(), $currentGroup->getLabel()->getString());
            }
        }
    }

    /**
     * @param BeforeListRenderEvent $event
     */
    public function onViewList(BeforeListRenderEvent $event)
    {
        $entity = $event->getEntity();

        if (!$entity instanceof AttributeFamilyAwareInterface) {
            return;
        }

        $groups = $this->attributeManager->getGroupsWithAttributes($entity->getAttributeFamily());
        $scrollData = $event->getScrollData();
        $this->filterGroupAttributes($groups, 'view', 'is_displayable');
        $this->addNotEmptyGroupBlocks($scrollData, $groups);

        /** @var AttributeGroup $group */
        foreach ($groups as $groupData) {
            /** @var AttributeGroup $group */
            $group = $groupData['group'];

            /** @var FieldConfigModel $attribute */
            foreach ($groupData['attributes'] as $attribute) {
                $fieldName = $attribute->getFieldName();
                if (in_array($fieldName, $this->getRestrictedToMoveFields(), true)) {
                    continue;
                }
                if ($scrollData->hasNamedField($fieldName)) {
                    $this->moveFieldToBlock($scrollData, $fieldName, $group->getCode());
                    continue;
                }

                $html = $event->getEnvironment()->render(
                    '@OroEntityConfig/Attribute/attributeView.html.twig',
                    [
                        'entity' => $entity,
                        'field' => $attribute,
                    ]
                );

                $subblockId = $scrollData->addSubBlock($group->getCode());
                $scrollData->addSubBlockData($group->getCode(), $subblockId, $html, $fieldName);
            }
        }

        $this->combineGroupBlocks($scrollData);
        $this->removeEmptyGroupBlocks($scrollData);
    }

    /**
     * @param ScrollData $scrollData
     * @param string $fieldId
     * @param string $blockId
     */
    protected function moveFieldToBlock(ScrollData $scrollData, $fieldId, $blockId)
    {
        if (in_array($fieldId, $this->getRestrictedToMoveFields(), true)) {
            return;
        }

        $data = $scrollData->getData();
        if (!isset($data[ScrollData::DATA_BLOCKS][$blockId])) {
            return;
        }

        foreach ($data[ScrollData::DATA_BLOCKS] as $currentBlockId => &$blockData) {
            foreach ($blockData[ScrollData::SUB_BLOCKS] as $subblockId => &$subblock) {
                if (isset($subblock[ScrollData::DATA][$fieldId])) {
                    $fieldData = $subblock[ScrollData::DATA][$fieldId];

                    if ($blockId !== $currentBlockId) {
                        unset($subblock[ScrollData::DATA][$fieldId]);

                        $subblockIds = $scrollData->getSubblockIds($blockId);
                        if (empty($subblockIds)) {
                            $subblockId = $scrollData->addSubBlock($blockId);
                        } else {
                            $subblockId = reset($subblockIds);
                        }

                        $scrollData->addSubBlockData($blockId, $subblockId, $fieldData, $fieldId);
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param array $groups
     * @param string $scope
     * @param string $option
     */
    private function filterGroupAttributes(array &$groups, $scope, $option)
    {
        foreach ($groups as &$group) {
            $group['attributes'] = array_filter(
                $group['attributes'],
                function (FieldConfigModel $attribute = null) use ($scope, $option) {
                    if ($attribute) {
                        $attributeScopedConfig = $attribute->toArray($scope);
                        return !empty($attributeScopedConfig[$option]);
                    }

                    return false;
                }
            );
        }
    }

    /**
     * @return array
     */
    protected function getRestrictedToMoveFields()
    {
        return $this->fieldsRestrictedToMove;
    }
}
