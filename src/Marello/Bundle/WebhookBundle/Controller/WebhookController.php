<?php

namespace Marello\Bundle\WebhookBundle\Controller;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Form\Type\WebhookType;
use Marello\Bundle\WebhookBundle\Model\WebhookEventInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\UIBundle\Route\Router;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_webhook_create"
     * )
     * @Template("@MarelloWebhook/Webhook/update.html.twig")
     * @AclAncestor("marello_webhook_create")
     *
     * @return array
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
     *
     * @param Request $request
     * @param Webhook|null $webhook
     *
     * @return array
     */
    public function updateAction(Request $request, Webhook $webhook = null)
    {
        return $this->update($request, $webhook);
    }

    /**
     * Handles order updates and creation.
     *
     * @param Request $request
     * @param Webhook|null $webhook
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function update(Request $request, Webhook $webhook = null)
    {
        $formClass = WebhookType::class;

        if ($webhook === null) {
            $webhook = new Webhook();
        }

        $form = $this->createForm($formClass, $webhook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.webhook.messages.success.webhook.saved')
            );
            $manager = $this->container->get(ManagerRegistry::class)->getManager();

            $manager->persist($webhook);
            $manager->flush();

            return $this->container->get(Router::class)->redirectAfterSave(
                [
                    'route'      => 'marello_webhook_update',
                    'parameters' => [
                        'id' => $webhook->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_webhook_view',
                    'parameters' => [
                        'id' => $webhook->getId(),
                    ],
                ],
                $webhook
            );
        }

        return [
            'entity' => $webhook,
            'form'   => $form->createView(),
        ];
    }

    /**
     * @deprecated
     * @param Webhook $webhook
     * @return \Extend\Entity\EV_Marello_Webhook_Event
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getWebhookEvent(Webhook $webhook): \Extend\Entity\EV_Marello_Webhook_Event
    {
        $doctrine = $this->container->get(ManagerRegistry::class);
        $eventEnumClass = ExtendHelper::buildEnumValueClassName(WebhookEventInterface::WEBHOOK_EVENT_ENUM_CLASS);
        return $doctrine
            ->getManagerForClass($eventEnumClass)
            ->getRepository($eventEnumClass)
            ->find($webhook->getEvent());
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
