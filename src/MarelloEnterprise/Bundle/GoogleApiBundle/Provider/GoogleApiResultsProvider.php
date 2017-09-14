<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Provider;

use MarelloEnterprise\Bundle\GoogleApiBundle\Client\Factory\GoogleApiClientFactoryInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\Factory\GoogleApiRequestFactoryInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GoogleApiResultFactoryInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

class GoogleApiResultsProvider implements GoogleApiResultsProviderInterface
{
    const FORMAT = 'json';

    /**
     * @var GoogleApiRequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var GoogleApiResultFactoryInterface
     */
    private $resultFactory;

    /**
     * @var GoogleApiClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @param GoogleApiRequestFactoryInterface $requestFactory
     * @param GoogleApiResultFactoryInterface $resultFactory
     * @param GoogleApiClientFactoryInterface $clientFactory
     */
    public function __construct(
        GoogleApiRequestFactoryInterface $requestFactory,
        GoogleApiResultFactoryInterface $resultFactory,
        GoogleApiClientFactoryInterface $clientFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->resultFactory = $resultFactory;
        $this->clientFactory = $clientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getApiResults(GoogleApiContextInterface $context)
    {
        $client = $this->clientFactory->createClient();
        $request = $this->requestFactory->createRequest($context);
        $params = $request->getRequestParameters();
        if (empty($params)) {
            return null;
        }
        try {
            $response = $client->get(self::FORMAT, $params);
        } catch (RestException $e) {
            return $this->resultFactory->createExceptionResult($e);
        }

        return $this->resultFactory->createResult($response, $context);
    }
}
