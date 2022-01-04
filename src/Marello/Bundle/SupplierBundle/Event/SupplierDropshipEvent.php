<?php

namespace Marello\Bundle\SupplierBundle\Event;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Symfony\Contracts\EventDispatcher\Event;

class SupplierDropshipEvent extends Event
{
    const NAME = 'marello_supplier.supplier_dropship_toggle';

    /**
     * @var Supplier
     */
    protected $supplier;

    /**
     * @var boolean
     */
    protected $canDropship;

    /**
     * @param Supplier $supplier
     * @param bool $canDropship
     */
    public function __construct(Supplier $supplier, $canDropship)
    {
        $this->supplier = $supplier;
        $this->canDropship = $canDropship;
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @return bool
     */
    public function isCanDropship()
    {
        return $this->canDropship;
    }
}
