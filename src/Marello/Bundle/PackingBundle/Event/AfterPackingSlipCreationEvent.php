<?php

namespace Marello\Bundle\PackingBundle\Event;

use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Symfony\Contracts\EventDispatcher\Event;

class AfterPackingSlipCreationEvent extends Event
{
    const NAME = 'marello_packing.after_packing_slip_creation';

    /**
     * @var PackingSlip
     */
    private $packingSlip;

    /**
     * @param PackingSlip $packingSlip
     */
    public function __construct(PackingSlip $packingSlip)
    {
        $this->packingSlip = $packingSlip;
    }

    public function setPackingSlip(PackingSlip $packingSlip = null)
    {
        $this->packingSlip = $packingSlip;
    }
    
    /**
     * @return PackingSlip|null
     */
    public function getPackingSlip()
    {
        return $this->packingSlip;
    }
}
