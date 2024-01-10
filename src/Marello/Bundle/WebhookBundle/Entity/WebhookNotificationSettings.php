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
    /**
     * @var ParameterBag
     */
    private $settings;

    /**
     * @return ParameterBag
     */
    public function getSettingsBag(): ParameterBag
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag([]);
        }

        return $this->settings;
    }
}
