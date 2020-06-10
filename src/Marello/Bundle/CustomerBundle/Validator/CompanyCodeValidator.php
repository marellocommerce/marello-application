<?php

namespace Marello\Bundle\CustomerBundle\Validator;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Validator\Constraints\CompanyCode;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CompanyCodeValidator extends ConstraintValidator
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof CompanyCode) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CompanyCode');
        }

        if (!$entity instanceof Company) {
            throw new UnexpectedTypeException($entity, Company::class);
        }
        $organization = $entity->getOrganization();
        $code = $entity->getCode();
        if ($organization && $code) {
            $existingCompanies = $this->doctrineHelper
                ->getEntityManagerForClass(Company::class)
                ->getRepository(Company::class)
                ->findBy(['code' => $code, 'organization' => $organization]);
            if (!empty($existingCompanies)) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('code')
                    ->addViolation();
            }
        }
    }
}
