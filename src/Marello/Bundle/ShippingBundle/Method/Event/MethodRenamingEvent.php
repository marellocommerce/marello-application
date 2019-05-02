<?php

namespace Marello\Bundle\ShippingBundle\Method\Event;

use Symfony\Component\EventDispatcher\Event;

class MethodRenamingEvent extends Event
{
    const NAME = 'marello_shipping.method_renaming';

    /**
     * @var string
     */
    private $oldMethodId;

    /**
     * @var string
     */
    private $newMethodId;

    /**
     * @param string $oldId
     * @param string $newId
     */
    public function __construct($oldId, $newId)
    {
        $this->oldMethodId = $oldId;
        $this->newMethodId = $newId;
    }

    /**
     * @return int|string
     */
    public function getOldMethodIdentifier()
    {
        return $this->oldMethodId;
    }

    /**
     * @return int|string
     */
    public function getNewMethodIdentifier()
    {
        return $this->newMethodId;
    }
}
