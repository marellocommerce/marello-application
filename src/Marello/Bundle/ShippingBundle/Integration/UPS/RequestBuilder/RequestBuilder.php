<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS\RequestBuilder;

use DOMDocument;
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

    public final function build(array $data)
    {
        $xml               = new DOMDocument();
        $xml->formatOutput = true;

        $this->buildAccessRequest($xml);
        $this->buildFunctionalityRequest($xml, $data);

        return $xml->saveXML();
    }

    protected function createFunctionalityRequestNode(DOMDocument $xml, $name, $action)
    {
        $xml->appendChild($fRequest = $xml->createElement($name));

        $fRequest->appendChild($request = $xml->createElement('Request'));

        $request->appendChild($transactionReference = $xml->createElement('TransactionReference'));
        $transactionReference->appendChild($xml->createElement('CustomerContext', 'Customer Context'));
        $transactionReference->appendChild($xml->createElement('XpciVersion', '1.0'));

        $request->appendChild($xml->createElement('RequestAction', $action));
        $request->appendChild($xml->createElement('RequestOption', 'validate'));

        return $fRequest;
    }

    private function buildAccessRequest(DOMDocument $xml)
    {
        $xml->appendChild($accessRequest = $xml->createElement('AccessRequest'));

        $accessRequest->appendChild($xml->createElement(
            'AccessLicenseNumber',
            $this->configManager->get('marello_shipping.ups_access_license_key')
        ));

        $accessRequest->appendChild($xml->createElement(
            'UserId',
            $this->configManager->get('marello_shipping.ups_username')
        ));

        $accessRequest->appendChild($xml->createElement(
            'Password',
            $this->configManager->get('marello_shipping.ups_password')
        ));
    }

    abstract protected function buildFunctionalityRequest(DOMDocument $xml, array $data);
}
