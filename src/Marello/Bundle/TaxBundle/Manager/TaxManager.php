<?php

namespace Marello\Bundle\TaxBundle\Manager;

use Marello\Bundle\TaxBundle\Event\TaxEventDispatcher;
use Marello\Bundle\TaxBundle\Factory\TaxFactory;
use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\Taxable;

class TaxManager
{

    /** @var TaxFactory */
    protected $taxFactory;

    /** @var TaxEventDispatcher */
    protected $eventDispatcher;

    /**
     * @param TaxFactory $taxFactory
     * @param TaxEventDispatcher $eventDispatcher
     */
    public function __construct(
        TaxFactory $taxFactory,
        TaxEventDispatcher $eventDispatcher
    ) {
        $this->taxFactory = $taxFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Calculate Result by object
     *
     * @param object $object
     * @return Result
     * @throws TaxationDisabledException if taxation disabled in system configuration
     */
    public function getTax($object)
    {
        return $this->getTaxable($object)->getResult();
    }

    /**
     * @param object $object
     * @return Taxable
     */
    protected function getTaxable($object)
    {
        $taxable = $this->taxFactory->create($object);
        $taxable->setResult(new Result());

        $this->eventDispatcher->dispatch($taxable);

        return $taxable;
    }
}
