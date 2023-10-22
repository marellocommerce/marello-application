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
            'headers' => [
                'User-Agent' => self::DEFAULT_USER_AGENT_HEADER,
            ],
            'authorization' => [
                'HTTP_MARELLO_SIGNATURE ' . $this->getMarelloWebhookSignature(),
            ]
        ];
    }

    protected function getMarelloWebhookSignature(): bool|string
    {
        $body = ''; // TODO: add real data here
        $eventName = $this->getWebhook()->getName();
        $secret = $this->getWebhook()->getSecret();

        return hash_hmac(self::DEFAULT_ALGO, $eventName.$body, $secret);
    }

    public function sendRequest($item): ?RestResponseInterface
    {
        $this->logger->notice('CALL: '.$this->getClientBaseUrl($this->settings) .' REQUEST: ' . json_encode($item, JSON_THROW_ON_ERROR));

        return $this->client->post(
            $this->getClientBaseUrl($this->settings),
            $item,
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
