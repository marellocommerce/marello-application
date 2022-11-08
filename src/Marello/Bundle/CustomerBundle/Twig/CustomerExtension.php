<?php

namespace Marello\Bundle\CustomerBundle\Twig;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Entity\Repository\CompanyRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomerExtension extends AbstractExtension
{
    const NAME = 'marello_customer';
    
    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    public function __construct(
        protected ManagerRegistry $registry
    ) {
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_get_company_children_ids',
                [$this, 'getCompanyChildrenIds']
            )
        ];
    }

    /**
     * {@inheritdoc}
     * @param int $companyId
     * @param bool $includeOwnId
     * @return array
     */
    public function getCompanyChildrenIds($companyId, $includeOwnId = true)
    {
        $children = $this->getRepository()->getChildrenIds($companyId);
        if ($includeOwnId) {
            $children[] = $companyId;
        }
        
        return $children;
    }

    protected function getRepository(): CompanyRepository
    {
        if (!$this->companyRepository) {
            $this->companyRepository = $this->registry->getRepository(Company::class);
        }

        return $this->companyRepository;
    }
}
