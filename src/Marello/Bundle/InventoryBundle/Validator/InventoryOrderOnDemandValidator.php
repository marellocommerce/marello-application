<?php

namespace Marello\Bundle\InventoryBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryOrderOnDemandValidator extends ConstraintValidator
{
    /** @var ConfigManager $configManager */
    private $configManager;

    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof InventoryItem) {
            return;
        }
        $product = $entity->getProduct();
        if ($this->configManager->get('marello_order.order_on_demand_enabled') &&
            $this->configManager->get('marello_order.inventoryorder_on_demand')
        ) {
            if ($entity->isOrderOnDemandAllowed() &&
                (!$product->getPreferredSupplier() || $product->getSuppliers()->count() === 0)) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('orderOnDemandAllowed')
                    ->addViolation();
            }
        }
        if ($entity->isOrderOnDemandAllowed() && $entity->isBackorderAllowed()) {
            $this->context
                ->buildViolation(
                    'marello.inventory.validation.messages.error.inventoryitem.order_on_demand_not_allowed_backorder'
                )
                ->atPath('orderOnDemandAllowed')
                ->addViolation();
        }
        if ($entity->isOrderOnDemandAllowed() && $entity->isCanPreorder()) {
            $this->context
                ->buildViolation(
                    'marello.inventory.validation.messages.error.inventoryitem.order_on_demand_not_allowed_preorder'
                )
                ->atPath('orderOnDemandAllowed')
                ->addViolation();
        }
    }

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }
}
