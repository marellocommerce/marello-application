<?php

namespace Marello\Bundle\WebhookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @ORM\Entity()
 */
class WebhookNotificationSettings extends Transport
{
    public const SETTINGS_FIELD_SIGNATURE_ALGO = 'webhookSignatureAlgo';

    /**
     * @var string
     *
     * @ORM\Column(name="webhook_signature_algo", type="string", length=1024, nullable=false)
     */
    private $webhookSignatureAlgo;

    /**
     * @var ParameterBag
     */
    private $settings;

    /**
     * @return string
     */
    public function getWebhookSignatureAlgo(): string
    {
        return $this->webhookSignatureAlgo;
    }

    /**
     * @param string $webhookSignatureAlgo
     */
    public function setWebhookSignatureAlgo(string $webhookSignatureAlgo): void
    {
        $this->webhookSignatureAlgo = $webhookSignatureAlgo;
    }

    /**
     * @return ParameterBag
     */
    public function getSettingsBag(): ParameterBag
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag([
                self::SETTINGS_FIELD_SIGNATURE_ALGO => $this->getWebhookSignatureAlgo(),
            ]);
        }

        return $this->settings;
    }
}
