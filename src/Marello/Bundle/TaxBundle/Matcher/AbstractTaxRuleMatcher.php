<?php

namespace Marello\Bundle\TaxBundle\Matcher;

use Marello\Bundle\TaxBundle\Entity\Repository\TaxRuleRepository;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

abstract class AbstractTaxRuleMatcher implements TaxRuleMatcherInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @return TaxRuleRepository
     */
    protected function getTaxRuleRepository()
    {
        return $this->doctrineHelper->getEntityRepositoryForClass(TaxRule::class);
    }
}
