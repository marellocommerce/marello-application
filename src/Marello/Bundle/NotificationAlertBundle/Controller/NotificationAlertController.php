<?php

namespace Marello\Bundle\NotificationAlertBundle\Controller;

use Marello\Bundle\NotificationAlertBundle\Entity\NotificationAlert;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NotificationAlertController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_notificationalert_index"
     * )
     * @Template("@MarelloNotificationAlert/NotificationAlert/index.html.twig")
     * @AclAncestor("marello_notificationalert_view")
     * @return array
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => NotificationAlert::class,
        ];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     name="marello_notificationalert_view",
     *     requirements={"id"="\d+"}
     * )
     * @Template("@MarelloNotificationAlert/NotificationAlert/view.html.twig")
     * @AclAncestor("marello_notificationalert_view")
     * @param NotificationAlert $entity
     * @return array
     */
    public function viewAction(NotificationAlert $entity): array
    {
        return ['entity' => $entity];
    }

    /**
     * @Route("/widget/info/{id}", name="marello_notificationalert_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("marello_notificationalert_view")
     * @Template("@MarelloNotificationAlert/NotificationAlert/widget/info.html.twig")
     * @param Request $request
     * @param NotificationAlert $entity
     * @return array
     */
    public function infoAction(Request $request, NotificationAlert $entity): array
    {
        $targetEntity = $this->getTargetEntity($request);
        $renderContexts = null !== $targetEntity;

        return [
            'entity' => $entity,
            'target' => $targetEntity,
            'renderContexts' => $renderContexts,
        ];
    }

    /**
     * @param Request $request
     * @return object|null
     */
    protected function getTargetEntity(Request $request)
    {
        $entityRoutingHelper = $this->container->get(EntityRoutingHelper::class);
        $targetEntityClass = $entityRoutingHelper->getEntityClassName($request, 'targetActivityClass');
        $targetEntityId = $entityRoutingHelper->getEntityId($request, 'targetActivityId');
        if (!$targetEntityClass || !$targetEntityId) {
            return null;
        }

        return $entityRoutingHelper->getEntity($targetEntityClass, $targetEntityId);
    }

    protected function getEventDispatcher()
    {
        return $this->container->get(EventDispatcherInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                EventDispatcherInterface::class,
                EntityRoutingHelper::class,
            ]
        );
    }
}
