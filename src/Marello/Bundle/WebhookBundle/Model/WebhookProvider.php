<?php

namespace Marello\Bundle\WebhookBundle\Model;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Form\Type\WebhookType;

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
     * @param int $status
     * @return array|object[]
     */
    public function getActiveWebhooks(int $status = WebhookType::ENABLE_STATUS)
    {
        return $this->entityManager->getRepository(Webhook::class)->findBy(['enabled' => $status]);
    }
}
