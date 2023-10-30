<?php

namespace Marello\Bundle\ReturnBundle\Manager\Rules;

use Doctrine\Persistence\ObjectManager;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Marello\Bundle\ReturnBundle\Manager\BusinessRuleInterface;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;

class MarelloProductWarranty implements BusinessRuleInterface
{
    const RETURN_ITEM_WARRANTY_REASON   = 'warranty';

    const RETURN_ITEM_ENUM_CODE         = 'marello_return_status';
    const RETURN_ITEM_STATUS_DENIED     = 'denied';
    const RETURN_ITEM_STATUS_AUTHORIZED = 'authorized';

    /** @var ConfigManager $configManager */
    protected $configManager;

    /** @var ObjectManager $objectManager */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     * @param ConfigManager $configManager
     */
    public function __construct(
        ObjectManager $objectManager,
        ConfigManager $configManager
    ) {
        $this->objectManager = $objectManager;
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     * @param ReturnEntity $entity
     * @return $this
     */
    public function applyRule($entity)
    {
        if (!$this->isApplicable()) {
            return $this;
        }

        $entity->getReturnItems()
            ->map(function (ReturnItem $returnItem) use ($entity) {
                if ($this->validateConditions($entity, $returnItem)) {
                    if (!$this->validateProductWarranty($entity, $returnItem)) {
                        $this->updateStatus(self::RETURN_ITEM_STATUS_DENIED, $returnItem);
                        return $this;
                    }
                    $this->updateStatus(self::RETURN_ITEM_STATUS_AUTHORIZED, $returnItem);
                }
            });

        return $this;
    }

    /**
     * {@inheritdoc}
     * @param ReturnEntity $return
     * @param ReturnItem $returnItem
     * @return bool
     */
    protected function validateConditions(ReturnEntity $return, ReturnItem $returnItem)
    {
        if (!$return->getOrder()->getPurchaseDate()) {
            return false;
        }

        if (!$returnItem->getReason()) {
            return false;
        }

        if ($returnItem->getReason()->getId() !== self::RETURN_ITEM_WARRANTY_REASON) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @param ReturnItem $returnItem
     * @return bool
     */
    protected function validateProductWarranty(ReturnEntity $return, ReturnItem $returnItem)
    {
        $purchaseDate  = $return->getOrder()->getPurchaseDate();
        $currentDate   = new \DateTime(date('Y-m-d'));

        /**
         * interval in days
         * @var \DateInterval $interval
         */
        $interval           = $currentDate->diff($purchaseDate);
        // take in account that months portion of interval cannot be greater than 12
        // so add the year into the equation
        $intervalInMonths   = ($interval->m + ($interval->y * 12));

        $systemWarrantyInMonths   = $this->configManager->get('marello_return.warranty_period');
        $productWarrantyInMonths = $returnItem->getOrderItem()->getProduct()->getWarranty();
        if (!$productWarrantyInMonths) {
            $productWarrantyInMonths = $systemWarrantyInMonths;
        }

        if ($intervalInMonths > $productWarrantyInMonths) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @param $enumValueId
     * @return null|object
     * @throws \Exception
     */
    private function getEnumvalueById($enumValueId)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::RETURN_ITEM_ENUM_CODE);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $this->objectManager->getRepository($className);
        $enumValue = $enumRepo->find($enumValueId);

        if (!$enumValue) {
            throw new \Exception(sprintf('Cannot find %s result for id %s', $className, $enumValueId));
        }

        return $enumValue;
    }

    /**
     * Update ReturnItem status
     * @param $statusCode
     * @param ReturnItem $item
     * @return $this
     * @throws \Exception
     */
    private function updateStatus($statusCode, $item)
    {
        $returnItemStatusEnum = $this->getEnumvalueById($statusCode);
        $item->setStatus($returnItemStatusEnum);

        // TODO:: verify if this is a preferable way to save these items
        $this->objectManager->persist($item);
        $this->objectManager->flush();

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    public function isApplicable()
    {
        return true;
    }
}
