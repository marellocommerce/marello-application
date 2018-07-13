<?php

namespace Marello\Bundle\UPSBundle\TimeInTransit;

use Marello\Bundle\UPSBundle\Client\Factory\UpsClientFactoryInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\TimeInTransit\Request\Factory\TimeInTransitRequestFactoryInterface;
use Marello\Bundle\UPSBundle\TimeInTransit\Result\Factory\TimeInTransitResultFactoryInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Psr\Log\LoggerInterface;

class TimeInTransitProvider implements TimeInTransitProviderInterface
{
    /**
     * @var TimeInTransitRequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var UpsClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var TimeInTransitResultFactoryInterface
     */
    private $resultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TimeInTransitRequestFactoryInterface $requestFactory
     * @param UpsClientFactoryInterface            $clientFactory
     * @param TimeInTransitResultFactoryInterface  $resultFactory
     * @param LoggerInterface                      $logger
     */
    public function __construct(
        TimeInTransitRequestFactoryInterface $requestFactory,
        UpsClientFactoryInterface $clientFactory,
        TimeInTransitResultFactoryInterface $resultFactory,
        LoggerInterface $logger
    ) {
        $this->requestFactory = $requestFactory;
        $this->clientFactory = $clientFactory;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeInTransitResult(
        UPSSettings $transport,
        AddressInterface $shipFromAddress,
        AddressInterface $shipToAddress,
        \DateTime $pickupDate,
        $weight
    ) {
        $request = $this->requestFactory->createRequest(
            $transport,
            $shipFromAddress,
            $shipToAddress,
            $pickupDate,
            $weight
        );
        $client = $this->clientFactory->createUpsClient($transport->isUpsTestMode());

        try {
            $response = $client->post($request->getUrl(), $request->getRequestData());
        } catch (RestException $e) {
            $this->logger->error($e->getMessage());

            return $this->resultFactory->createExceptionResult($e);
        }

        return $this->resultFactory->createResultByUpsClientResponse($response);
    }
}
