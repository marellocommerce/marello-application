<?php

namespace Marello\Bundle\PaymentBundle\Method\Event;

use Symfony\Contracts\EventDispatcher\Event;

class MethodRenamingEvent extends Event
{
    const NAME = 'marello_payment.method_renaming';

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
     * @return string
     */
    public function getOldMethodIdentifier()
    {
        return $this->oldMethodId;
    }

    /**
     * @return string
     */
    public function getNewMethodIdentifier()
    {
        return $this->newMethodId;
    }
}
