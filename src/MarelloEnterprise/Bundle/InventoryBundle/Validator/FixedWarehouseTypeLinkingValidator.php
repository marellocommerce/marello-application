<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator;

use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\FixedWarehouseTypeLinking;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FixedWarehouseTypeLinkingValidator extends ConstraintValidator
{
    const ALIAS = 'marelloenterprise_inventory.fixed_warehouse_type_linking';

    /**
     * @var ExecutionContextInterface
     */
    protected $context;
    
    /**
     * @var IsFixedWarehouseGroupChecker
     */
    protected $checker;

    /**
     * @param IsFixedWarehouseGroupChecker $checker
     */
    public function __construct(IsFixedWarehouseGroupChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * @param mixed $value
     * @param FixedWarehouseTypeLinking|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof WarehouseChannelGroupLink) {
            throw new UnexpectedTypeException($value, WarehouseChannelGroupLink::class);
        }

        if ($this->checker->check($value->getWarehouseGroup()) && $value->getSalesChannelGroups()->count() > 1) {
            $this->context->buildViolation($constraint->message)
                ->atPath('salesChannelGroups')
                ->addViolation();
        }
    }
}
