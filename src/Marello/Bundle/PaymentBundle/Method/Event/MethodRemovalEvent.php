<?php

namespace Marello\Bundle\PaymentBundle\Method\Event;

use Symfony\Contracts\EventDispatcher\Event;

class MethodRemovalEvent extends Event
{
    const NAME = 'marello_payment.method_removal';

    /**
     * @var int|string
     */
    private $methodId;

    /**
     * @param int|string $id
     */
    public function __construct($id)
    {
        $this->methodId = $id;
    }

    /**
     * @return int|string
     */
    public function getMethodIdentifier()
    {
        return $this->methodId;
    }
}
