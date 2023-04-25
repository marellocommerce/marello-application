<?php

namespace Marello\Bundle\WebhookBundle\Integration\Transport;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Entity\WebhookNotificationSettings;
use Marello\Bundle\WebhookBundle\Form\Type\WebhookSettingsType;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\AbstractRestTransport;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use JsonException;

/**
 * Class responsible for creating and making requests to Remote API
 */
class WebhookTransport extends AbstractRestTransport
{
    use LoggerAwareTrait;

    public const DEFAULT_ALGO = 'sha256';

    /** @var Webhook */
    protected Webhook $webhook;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType(): string
    {
        return WebhookSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN(): string
    {
        return WebhookNotificationSettings::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'marello.webhook.notification.integration.label';
    }

    protected function getClientBaseUrl(ParameterBag $parameterBag)
    {
        return $this->getWebhook()->getCallbackUrl();
    }

    /**
     * {@inheritdoc}
     */
    protected function getClientOptions(ParameterBag $parameterBag)
    {
        return [
            'HTTP_MARELLO_SIGNATURE' => $this->getMarelloWebhookSignature($parameterBag),
        ];
    }

    /**
     * @param ParameterBag $parameterBag
     * @return false|string
     */
    public function getMarelloWebhookSignature(ParameterBag $parameterBag): bool|string
    {
        $body = ''; //TODO: add real data here
        $eventName = $this->getWebhook()->getName();
        $secret = $this->getWebhook()->getSecret();
        $algorithm = $parameterBag->get('webhook_signature_algo') ?? self::DEFAULT_ALGO;
        return hash_hmac($algorithm, $eventName.$body, $secret);
    }

    /**
     * @throws RestException|JsonException
     */
    public function sendRequest($item): ?RestResponseInterface
    {
        //LOG
        $this->logger->notice('CALL: '.$this->getClientBaseUrl($this->settings) .' REQUEST: ' . json_encode($item, JSON_THROW_ON_ERROR));

        return $this->client->post(
            $this->getClientBaseUrl($this->settings),
            $item,
            $this->getClientOptions($this->settings)
        );
    }


    /**
     * @return Webhook
     */
    public function getWebhook(): Webhook
    {
        return $this->webhook;
    }

    /**
     * @param Webhook $webhook
     */
    public function setWebhook(Webhook $webhook): void
    {
        $this->webhook = $webhook;
    }
}
