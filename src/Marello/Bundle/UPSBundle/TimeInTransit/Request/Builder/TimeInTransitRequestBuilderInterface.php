<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit\Request\Builder;

use Marello\Bundle\UPSBundle\Client\Request\UpsClientRequestInterface;

interface TimeInTransitRequestBuilderInterface
{
    /**
     * @return UpsClientRequestInterface
     */
    public function createRequest();

    /**
     * @param int $weight
     * @param string $weightUnitCode
     *
     * @return $this
     */
    public function setWeight($weight, $weightUnitCode);

    /**
     * @param string $maximumListSize
     *
     * @return $this
     */
    public function setMaximumListSize($maximumListSize);

    /**
     * @param string $transactionIdentifier
     *
     * @return $this
     */
    public function setTransactionIdentifier($transactionIdentifier);

    /**
     * @param string $customerContext
     *
     * @return $this
     */
    public function setCustomerContext($customerContext);
}
