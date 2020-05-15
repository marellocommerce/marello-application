<?php

namespace Marello\Bundle\Magento2Bundle\Exception;

use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Throwable;

class RuntimeException extends \Exception
{
    /**
     * @var bool
     */
    protected $isTransportException = false;

    /**
     * {@inheritDoc}
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->isTransportException = $previous instanceof TransportException;
    }

    /**
     * @return bool
     */
    public function isTransportException(): bool
    {
        return $this->isTransportException;
    }
}
