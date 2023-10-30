<?php

namespace Marello\Bundle\WebhookBundle\Controller;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Form\Type\WebhookType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Oro\Bundle\UIBundle\Route\Router;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebhookController extends AbstractController
{
    /**
     * @Route("/", name="marello_webhook_index")
     * @Template
     * @AclAncestor("marello_webhook_view")
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
     */
    public function viewAction(Webhook $webhook): array
    {
        return [
            'entity' => $webhook,
        ];
    }

    /**
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_webhook_create"
     * )
     * @Template("@MarelloWebhook/Webhook/update.html.twig")
     * @AclAncestor("marello_webhook_create")
     */
    public function createAction(Request $request, Webhook $webhook = null)
    {
        return $this->update($request, $webhook);
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_webhook_update"
     * )
     * @Template
     * @AclAncestor("marello_webhook_update")
     */
    public function updateAction(Request $request, Webhook $webhook = null)
    {
        return $this->update($request, $webhook);
    }

    /**
     * @Route(
     *     path="/delete/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_webhook_delete",
     *     methods={"DELETE"}
     * )
     * @CsrfProtection()
     * @AclAncestor("marello_webhook_delete")
     */
    public function deleteAction(Webhook $webhook): JsonResponse
    {
        $translator = $this->container->get(TranslatorInterface::class);
        if ($this->isGranted('delete', $webhook)) {
            $registry = $this->container->get(ManagerRegistry::class);
            $entityManager = $registry->getManagerForClass(Webhook::class);
            $entityManager->remove($webhook);
            $entityManager->flush();

            $successful = true;
            $message = $translator->trans('marello.webhook.webhook_entity.message.deleted');
        } else {
            $successful = false;
            $message = $translator->trans('marello.webhook.webhook_entity.message.cant_delete');
        }

        return new JsonResponse(['message' => $message, 'successful' => $successful]);
    }

    protected function update(Request $request, Webhook $webhook = null)
    {
        if ($webhook === null) {
            $webhook = new Webhook();
        }

        $form = $this->createForm(WebhookType::class, $webhook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.webhook.messages.success.webhook.saved')
            );

            $manager = $this->container->get(ManagerRegistry::class)->getManager();
            $manager->persist($webhook);
            $manager->flush();

            return $this->container->get(Router::class)->redirect($webhook);
        }

        return [
            'entity' => $webhook,
            'form'   => $form->createView(),
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                Router::class,
                ManagerRegistry::class,
            ]
        );
    }
}
