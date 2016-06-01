<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder;

use DOMDocument;

class RequestBuilder
{
    /**
     * @var string
     */
    protected $customerContext;

    /**
     * @param string $customerContext
     *
     * @return $this
     */
    public function setCustomerContext($customerContext)
    {
        $this->customerContext = $customerContext;

        return $this;
    }

    protected function createTransactionNode()
    {
        $xml               = new DOMDocument();
        $xml->formatOutput = true;

        $transaction = $xml->appendChild($xml->createElement('TransactionReference'));

        if (null !== $this->customerContext) {
            $transaction->appendChild($xml->createElement('CustomerContext', $this->customerContext));
        }

        return $transaction->cloneNode(true);
    }
}
