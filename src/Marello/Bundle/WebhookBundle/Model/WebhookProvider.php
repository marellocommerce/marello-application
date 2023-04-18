<?php

namespace Marello\Bundle\WebhookBundle\Model;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Form\Type\WebhookType;
use Marello\Bundle\WebhookBundle\Integration\WebhookChannel;
use Oro\Bundle\AkeneoBundle\Integration\AkeneoChannel;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;

class WebhookProvider
{
    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $eventName
     * @param boolean $status
     * @return array
     */
    public function getActiveWebhooks($eventName, bool $status = true): array
    {
        return $this->entityManager->getRepository(Webhook::class)->findBy(['enabled' => $status, 'event' => $eventName]);
    }

    /**
     * @param string $type
     * @return array
     */
    public function getWebhookIntergrations(string $type = WebhookChannel::TYPE): array
    {
        /** @var ChannelRepository $integrationRepository */
        $integrationRepository = $this->entityManager->getRepository(Integration::class);

        return $integrationRepository->getConfiguredChannelsForSync($type, true);
    }
}
