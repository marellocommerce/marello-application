<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\DefaultWarehouseExists;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class DefaultWarehouseExistsValidator extends ConstraintValidator
{
    /**
     * @var ExecutionContextInterface
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Checks if there is already a default warehouse selected.
     *
     * @param Warehouse                         $value
     * @param DefaultWarehouseExists|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value->getId() && !$value->isDefault()) {
            /** @var EntityManagerInterface $em */
            $em = $this->registry->getManager();

            $qb = $em->createQueryBuilder();
            $qb
                ->select($qb->expr()->count('w'))
                ->from('MarelloInventoryBundle:Warehouse', 'w')
                ->where($qb->expr()->eq('w.default', $qb->expr()->literal(true)))
                ->andWhere($qb->expr()->not($qb->expr()->eq('w.id', $value->getId())));

            $count = $qb->getQuery()->getSingleScalarResult();

            if (!$count) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('default')
                    ->addViolation();
            }
        }
    }
}
