<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\DefaultWarehouseExists;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DefaultWarehouseExistsValidator extends ConstraintValidator
{
    const ALIAS = 'marelloenterprise_inventory.default_warehouse_exists';

    public function __construct(
        protected WarehouseRepository $warehouseRepository,
        protected AclHelper $aclHelper
    ) {
    }

    /**
     * @param mixed $value
     * @param DefaultWarehouseExists|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Warehouse) {
            throw new UnexpectedTypeException($value, Warehouse::class);
        }
        if ($value->getId() && !$value->isDefault()) {
            if (count($this->warehouseRepository->getDefaultExcept($value->getId(), $this->aclHelper)) === 0) {
                /** @var ExecutionContextInterface $context */
                $context = $this->context;
                $context->buildViolation($constraint->message)
                    ->atPath('default')
                    ->addViolation();
            }
        }
    }
}
