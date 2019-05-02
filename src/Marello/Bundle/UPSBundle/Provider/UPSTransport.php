<?php

namespace Marello\Bundle\UPSBundle\Provider;

use Marello\Bundle\UPSBundle\Client\Url\Provider\UpsClientUrlProviderInterface;
use Marello\Bundle\UPSBundle\Form\Type\UPSTransportSettingsType;
use Marello\Bundle\UPSBundle\Model\Request\PriceRequest;
use Marello\Bundle\UPSBundle\Model\Request\ShipmentAcceptRequest;
use Marello\Bundle\UPSBundle\Model\Request\ShipmentConfirmRequest;
use Marello\Bundle\UPSBundle\Model\Response\PriceResponse;
use Marello\Bundle\UPSBundle\Model\Response\ShipmentAcceptResponse;
use Marello\Bundle\UPSBundle\Model\Response\ShipmentConfirmResponse;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\AbstractRestTransport;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class UPSTransport extends AbstractRestTransport
{
    const API_JSON_PREFIX = 'rest';
    const API_XML_PREFIX = 'ups.app/xml';

    const API_RATES_PREFIX = 'Rate';
    const API_SHIP_CONFIRM_PREFIX = 'ShipConfirm';
    const API_SHIP_ACCEPT_PREFIX = 'ShipAccept';

    /**
     * @var UpsClientUrlProviderInterface
     */
    private $upsClientUrlProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param UpsClientUrlProviderInterface $upsClientUrlProvider
     * @param LoggerInterface               $logger
     */
    public function __construct(UpsClientUrlProviderInterface $upsClientUrlProvider, LoggerInterface $logger)
    {
        $this->upsClientUrlProvider = $upsClientUrlProvider;
        $this->logger = $logger;
    }

    /**
     * @param ParameterBag $parameterBag
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getClientBaseUrl(ParameterBag $parameterBag)
    {
        return $this->upsClientUrlProvider->getUpsUrl($parameterBag->get('test_mode'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getClientOptions(ParameterBag $parameterBag)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.ups.transport.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return UPSTransportSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'Marello\Bundle\UPSBundle\Entity\UPSSettings';
    }

    /**
     * @param PriceRequest $priceRequest
     * @param Transport $transportEntity
     * @throws RestException
     * @throws InvalidConfigurationException
     * @throws \InvalidArgumentException
     * @return PriceResponse|null
     */
    public function getPriceResponse(PriceRequest $priceRequest, Transport $transportEntity)
    {
        try {
            $apiPrefix = sprintf('%s/%s', self::API_JSON_PREFIX, static::API_RATES_PREFIX);
            $this->client = $this->createRestClient($transportEntity);
            $response = $this->client->post($apiPrefix, $priceRequest->stringify());

            if (!is_array($response->json())) {
                return null;
            }

            return (new PriceResponse())->parse($response);
        } catch (\LogicException $e) {
            $this->logger->error(
                sprintf('Price request failed for transport #%s. %s', $transportEntity->getId(), $e->getMessage())
            );
        } catch (RestException $restException) {
            $this->logger->error(
                sprintf(
                    'Price REST request failed for transport #%s. %s',
                    $transportEntity->getId(),
                    $restException->getMessage()
                )
            );
        }

        return null;
    }

    /**
     * @param ShipmentConfirmRequest $request
     * @param Transport $transportEntity
     * @throws RestException
     * @throws InvalidConfigurationException
     * @throws \InvalidArgumentException
     * @return ShipmentConfirmResponse|null
     */
    public function getShipmentConfirmResponse(ShipmentConfirmRequest $request, Transport $transportEntity)
    {
        $headers = [
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
            'Access-Control-Allow-Methods' => 'POST',
            'Access-Control-Allow-Origin'  => '*',
            'Content-Type'                 => 'Application/x-www-form-urlencoded',
        ];
        try {
            $apiPrefix = sprintf('%s/%s', self::API_XML_PREFIX, static::API_SHIP_CONFIRM_PREFIX);
            $this->client = $this->createRestClient($transportEntity);
            $response = $this->client->post($apiPrefix, $request->stringify(), $headers);

            return (new ShipmentConfirmResponse())->parse($response);
        } catch (\LogicException $e) {
            $this->logger->error(
                sprintf(
                    'ShipmentConfirm request failed for transport #%s. %s',
                    $transportEntity->getId(),
                    $e->getMessage()
                )
            );
        } catch (RestException $restException) {
            $this->logger->error(
                sprintf(
                    'ShipmentConfirm REST request failed for transport #%s. %s',
                    $transportEntity->getId(),
                    $restException->getMessage()
                )
            );
        }

        return null;
    }

    /**
     * @param ShipmentAcceptRequest $request
     * @param Transport $transportEntity
     * @throws RestException
     * @throws InvalidConfigurationException
     * @throws \InvalidArgumentException
     * @return ShipmentAcceptResponse|null
     */
    public function getShipmentAcceptResponse(ShipmentAcceptRequest $request, Transport $transportEntity)
    {
        $headers = [
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
            'Access-Control-Allow-Methods' => 'POST',
            'Access-Control-Allow-Origin'  => '*',
            'Content-Type'                 => 'Application/x-www-form-urlencoded',
        ];
        try {
            $apiPrefix = sprintf('%s/%s', self::API_XML_PREFIX, static::API_SHIP_ACCEPT_PREFIX);
            $this->client = $this->createRestClient($transportEntity);
            $response = $this->client->post($apiPrefix, $request->stringify(), $headers);

            return (new ShipmentAcceptResponse())->parse($response);
        } catch (\LogicException $e) {
            $this->logger->error(
                sprintf(
                    'ShipmentAccept request failed for transport #%s. %s',
                    $transportEntity->getId(),
                    $e->getMessage()
                )
            );
        } catch (RestException $restException) {
            $this->logger->error(
                sprintf(
                    'ShipmentAccept REST request failed for transport #%s. %s',
                    $transportEntity->getId(),
                    $restException->getMessage()
                )
            );
        }

        return null;
    }
}
