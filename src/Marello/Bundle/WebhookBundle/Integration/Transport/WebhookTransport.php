<?php

namespace Marello\Bundle\WebhookBundle\Integration\Transport;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Form\Type\WebhookSettingsType;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\AbstractRestTransport;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class responsible for creating and making requests to Remote API
 */
class WebhookTransport extends AbstractRestTransport
{
    public const DEFAULT_ALGO = 'sha256';
    public const DEFAULT_USER_AGENT_HEADER = 'Marello-Webhook-App';
    public const SETTINGS_PAYLOAD_KEY = 'payload';

    /**
     * @var Webhook
     */
    protected Webhook $webhook;

    public function __construct(
        protected LoggerInterface $logger
    ) {}

    public function getSettingsFormType(): string
    {
        return WebhookSettingsType::class;
    }

    public function getSettingsEntityFQCN(): string
    {
        return 'Marello\Bundle\WebhookBundle\Entity\WebhookNotificationSettings';
    }

    public function getLabel(): string
    {
        return 'marello.webhook.notification.integration.label';
    }

    protected function getClientBaseUrl(ParameterBag $parameterBag)
    {
        return $this->getWebhook()->getCallbackUrl();
    }

    protected function getClientOptions(ParameterBag $parameterBag)
    {
        return [
            'User-Agent' => [
                self::DEFAULT_USER_AGENT_HEADER
            ],
            'X-Marello-Signature' => [
                $this->getMarelloWebhookSignature($parameterBag)
            ]
        ];
    }

    protected function getMarelloWebhookSignature(ParameterBag $parameterBag): bool|string
    {
        $body = $parameterBag->get(self::SETTINGS_PAYLOAD_KEY);
        $secret = $this->getWebhook()->getSecret();

        return base64_encode(
            hash_hmac(self::DEFAULT_ALGO, $body, $secret, true)
        );
    }

    public function sendRequest($item): ?RestResponseInterface
    {
        $jsonData = json_encode($item, JSON_THROW_ON_ERROR);
        $this->settings->set(self::SETTINGS_PAYLOAD_KEY, $jsonData);
        $this->logger->notice('CALL: '.$this->getClientBaseUrl($this->settings) .' REQUEST: ' . $jsonData);

        return $this->client->post(
            $this->getClientBaseUrl($this->settings),
            $jsonData,
            $this->getClientOptions($this->settings)
        );
    }

    public function getWebhook(): Webhook
    {
        return $this->webhook;
    }

    public function setWebhook(Webhook $webhook): void
    {
        $this->webhook = $webhook;
    }
}
