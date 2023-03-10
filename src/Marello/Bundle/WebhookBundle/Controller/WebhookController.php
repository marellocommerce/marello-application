<?php

namespace Marello\Bundle\WebhookBundle\Controller;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class WebhookController extends AbstractController
{
    /**
     * @Route("/", name="marello_webhook_index")
     * @Template
     * @AclAncestor("marello_webhook_view")
     *
     * @return array
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Webhook::class
        ];
    }
}
