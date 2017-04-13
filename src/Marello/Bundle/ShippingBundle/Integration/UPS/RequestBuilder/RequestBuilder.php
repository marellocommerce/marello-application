<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder;

use DOMDocument;
use Marello\Bundle\ShippingBundle\Integration\UPS\Model\AccessRequest;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

abstract class RequestBuilder
{
    /** @var ConfigManager */
    protected $configManager;

    /**
     * RequestBuilder constructor.
     *
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * Builds request document.
     *
     * @param array $data
     *
     * @return string
     */
    final public function build(array $data)
    {
        $xml               = new DOMDocument();
        $xml->formatOutput = true;

        $this->buildAccessRequest($xml);
        $this->buildFunctionalityRequest($xml, $data);

        return $xml->saveXML();
    }

    /**
     * @param DOMDocument $xml
     * @param string      $name
     * @param string      $action
     *
     * @return \DOMElement
     */
    protected function createFunctionalityRequestNode(DOMDocument $xml, $name, $action)
    {
        $xml->appendChild($fRequest = $xml->createElement($name));

        $fRequest->setAttribute('xml:lang', 'en-US');

        $fRequest->appendChild($request = $xml->createElement('Request'));

        $request->appendChild($transactionReference = $xml->createElement('TransactionReference'));
        $transactionReference->appendChild($xml->createElement('CustomerContext', 'Customer Context'));
        $transactionReference->appendChild($xml->createElement('XpciVersion'));

        $request->appendChild($xml->createElement('RequestAction', $action));
        $request->appendChild($xml->createElement('RequestOption', 'validate'));

        return $fRequest;
    }

    /**
     * @param DOMDocument $xml
     */
    private function buildAccessRequest(DOMDocument $xml)
    {
        $accessRequest = new AccessRequest(
            $this->configManager->get('marello_shipping.ups_username'),
            $this->configManager->get('marello_shipping.ups_password'),
            $this->configManager->get('marello_shipping.ups_access_license_key')
        );

        $xml->appendChild($accessRequest->toXmlNode($xml));
    }

    /**
     * @param DOMDocument $xml
     * @param array       $data
     */
    abstract protected function buildFunctionalityRequest(DOMDocument $xml, array $data);
}
