<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\WarehouseAddedToLinkedGroup;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WarehouseAddedToLinkedGroupValidator extends ConstraintValidator
{
    const ALIAS = 'marelloenterprise_inventory.warehouse_added_to_linked_group';

    /**
     * @var ExecutionContextInterface
     */
    protected $context;

    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param mixed $value
     * @param WarehouseAddedToLinkedGroup|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Warehouse) {
            throw new UnexpectedTypeException($value, Warehouse::class);
        }
        $group = $value->getGroup();
        if ($group && !$group->isSystem() && $group->getWarehouseChannelGroupLink()) {
            /** @var Warehouse $oldValue */
            $oldValue = $this->manager
                ->getUnitOfWork()
                ->getOriginalEntityData($value);
            if ($value->getWarehouseType()->getName() !== $oldValue['warehouse_type']) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('warehouseType')
                    ->addViolation();
            }
        }
    }
}
