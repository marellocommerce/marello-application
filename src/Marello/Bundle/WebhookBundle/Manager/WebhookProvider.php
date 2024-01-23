<?php

namespace Marello\Bundle\WebhookBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Integration\WebhookChannel;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;

class WebhookProvider
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {}

    public function getActiveWebhooks(string $eventName, bool $status = true): array
    {
        $criteria = ['enabled' => $status, 'event' => $eventName];

        return $this->entityManager->getRepository(Webhook::class)->findBy($criteria);
    }

    public function getWebhookIntergrations(string $type = WebhookChannel::TYPE): array
    {
        /** @var ChannelRepository $integrationRepository */
        $integrationRepository = $this->entityManager->getRepository(Integration::class);

        return $integrationRepository->getConfiguredChannelsForSync($type, true);
    }

    public function getWebhookById(int $id): ?Webhook
    {
        return $this->entityManager->getRepository(Webhook::class)->find($id);
    }

    public function getWebhookIntergrationById(int $id): ?Integration
    {
        return $this->entityManager->getRepository(Integration::class)->find($id);
    }
}
