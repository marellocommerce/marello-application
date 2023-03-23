<?php

namespace Marello\Bundle\WebhookBundle\Controller;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_webhook_view"
     * )
     * @AclAncestor("marello_webhook_view")
     * @Template("@MarelloWebhook/Webhook/view.html.twig")
     *
     * @param Webhook $webhook
     * @return array
     */
    public function viewAction(Webhook $webhook)
    {
        return [
            'entity' => $webhook,
        ];
    }


    /**
     * @Route(
     *     path="/update/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_webhook_update"
     * )
     * @Template
     * @AclAncestor("marello_webhook_update")
     *
     * @param Request $request
     * @param Webhook|null $webhook
     *
     * @return array
     */
    public function updateAction(Request $request, Webhook $webhook = null)
    {
        return [];
//        return $this->update($request, $webhook);
    }
}
