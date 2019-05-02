<?php

namespace Marello\Bundle\TaxBundle\Event;

use Marello\Bundle\TaxBundle\Model\Taxable;
use Symfony\Component\EventDispatcher\Event;

class ResolveTaxEvent extends Event
{
    const RESOLVE_BEFORE = 'marello_tax.resolve_before';
    const RESOLVE = 'marello_tax.resolve';
    const RESOLVE_AFTER = 'marello_tax.resolve_after';

    /** @var Taxable */
    protected $taxable;

    /**
     * @param Taxable $taxable
     */
    public function __construct(Taxable $taxable)
    {
        $this->taxable = $taxable;
    }

    /**
     * @return Taxable
     */
    public function getTaxable()
    {
        return $this->taxable;
    }
}
