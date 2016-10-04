<?php

namespace Marello\Bundle\ReturnBundle\Util;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;

class ReturnHelper
{
    /**
     * @var array
     *      key   => enum value entity class name
     *      value => array // values are sorted by priority
     *          key   => enum value id
     *          value => enum value name
     *
     */
    protected $localCache = [];

    /** @var ObjectManager $objectManager */
    protected $objectManager;

    /**
     * ReturnHelper constructor.
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns amount of already returned items for given order item.
     *
     * @param OrderItem $orderItem
     *
     * @return int
     */
    public function getOrderItemReturnedQuantity(OrderItem $orderItem)
    {
        $sum = 0;

        $orderItem
            ->getReturnItems()
            ->map(function (ReturnItem $returnItem) use (&$sum) {
                $sum += $returnItem->getQuantity();
            });

        return $sum;
    }

    /**
     * Get ReturnItem return reason enum values
     * @return array
     */
    public function getReturnReasonEnumValues()
    {
        return $this->getEnumValues('marello_return_reason');
    }

    /**
     * @param $enumValueEntityClassOrEnumCode
     *
     * @return array sorted by value priority
     *      key   => enum value id
     *      value => enum value name
     */
    private function getEnumValues($enumValueEntityClassOrEnumCode)
    {
        if (strpos($enumValueEntityClassOrEnumCode, '\\') === false) {
            $enumValueEntityClassOrEnumCode = ExtendHelper::buildEnumValueClassName($enumValueEntityClassOrEnumCode);
        }
        if (!isset($this->localCache[$enumValueEntityClassOrEnumCode])) {
            $items      = [];
            /** @var AbstractEnumValue[] $values */
            $values = $this->objectManager->getRepository($enumValueEntityClassOrEnumCode)->findAll();
            usort($values, function ($value1, $value2) {
                return $value1->getPriority() >= $value2->getPriority();
            });
            foreach ($values as $value) {
                $items[$value->getId()] = $value->getName();
            }
            $this->localCache[$enumValueEntityClassOrEnumCode] = $items;
        }
        return $this->localCache[$enumValueEntityClassOrEnumCode];
    }
}
