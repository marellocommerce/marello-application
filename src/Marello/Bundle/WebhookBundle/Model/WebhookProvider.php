<?php

namespace Marello\Bundle\WebhookBundle\Model;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Integration\WebhookChannel;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;

class WebhookProvider
{
    /** @var EntityManager */
    protected EntityManager $entityManager;

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
        $criteria = ['enabled' => $status, 'event' => $eventName];
        return $this->entityManager->getRepository(Webhook::class)->findBy($criteria);
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

    /**
     * @param $id
     * @return Webhook
     */
    public function getWebhookById($id): Webhook
    {
        return $this->entityManager->getRepository(Webhook::class)->find($id);
    }

    /**
     * @param $id
     * @return Integration
     */
    public function getWebhookIntergrationById($id): Integration
    {
        return $this->entityManager->getRepository(Integration::class)->find($id);
    }
}
