<?php

namespace Marello\Bundle\CustomerBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Validator\Constraints\CompanyNumber;

class CompanyNumberValidator extends ConstraintValidator
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
        if (!$constraint instanceof CompanyNumber) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CompanyNumber');
        }

        if (!$entity instanceof Company) {
            throw new UnexpectedTypeException($entity, Company::class);
        }
        $organization = $entity->getOrganization();
        $companyNumber = $entity->getCompanyNumber();
        if ($organization && $companyNumber) {
            $existingCompanies = $this->doctrineHelper
                ->getEntityManagerForClass(Company::class)
                ->getRepository(Company::class)
                ->findBy(['companyNumber' => $companyNumber, 'organization' => $organization]);
            if (!empty($existingCompanies)) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('companyNumber')
                    ->addViolation();
            }
        }
    }
}
