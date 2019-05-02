<?php

namespace Marello\Bundle\TaxBundle\Resolver;

use Marello\Bundle\TaxBundle\Matcher\TaxRuleMatcherInterface;

abstract class AbstractItemResolver implements ResolverInterface
{
    /**
     * @var RowTotalResolver
     */
    protected $rowTotalResolver;

    /**
     * @var TaxRuleMatcherInterface
     */
    protected $matcher;

    /**
     * @param RowTotalResolver $rowTotalResolver
     * @param TaxRuleMatcherInterface $matcher
     */
    public function __construct(
        RowTotalResolver $rowTotalResolver,
        TaxRuleMatcherInterface $matcher
    ) {
        $this->rowTotalResolver = $rowTotalResolver;
        $this->matcher = $matcher;
    }
}
