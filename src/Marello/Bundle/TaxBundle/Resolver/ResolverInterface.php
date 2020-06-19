<?php

namespace Marello\Bundle\TaxBundle\Resolver;

use Marello\Bundle\TaxBundle\Model\Taxable;

interface ResolverInterface
{
    /**
     * @param Taxable $taxable
     */
    public function resolve(Taxable $taxable);
}
