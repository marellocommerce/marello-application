<?php

namespace Marello\Bundle\NotificationMessageBundle\Controller;

use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Entity\Repository\NotificationMessageRepository;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationMessageController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_notificationmessage_index"
     * )
     * @Template("@MarelloNotificationMessage/NotificationMessage/index.html.twig")
     * @AclAncestor("marello_notificationmessage_view")
     * @return array
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => NotificationMessage::class,
        ];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     name="marello_notificationmessage_view",
     *     requirements={"id"="\d+"}
     * )
     * @Template("@MarelloNotificationMessage/NotificationMessage/view.html.twig")
     * @AclAncestor("marello_notificationmessage_view")
     * @param NotificationMessage $entity
     * @return array
     */
    public function viewAction(NotificationMessage $entity): array
    {
        return ['entity' => $entity];
    }

    /**
     * @Route(
     *     "/widget/sidebar-notification-messages/{perPage}",
     *     name="marello_notificationmessage_widget_sidebar_notification_messages",
     *     defaults={"perPage" = 10},
     *     requirements={"perPage"="\d+"}
     * )
     * @AclAncestor("marello_notificationmessage_view")
     */
    public function notificationMessagesWidgetAction(Request $request, int $perPage): Response
    {
        /** @var NotificationMessageRepository $repository */
        $repository = $this->container->get('doctrine')->getRepository(NotificationMessage::class);
        /** @var User $user */
        $user = $this->getUser();
        $types = $this->extractTypes($request);
        $notificationMessages = $repository->getNotificationMessagesAssignedTo($user, $perPage, $types);

        return $this->render(
            '@MarelloNotificationMessage/NotificationMessage/widget/notificationMessagesWidget.html.twig',
            ['notificationMessages' => $notificationMessages]
        );
    }

    protected function extractTypes(Request $request): array
    {
        $possibleTypes = [
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ERROR,
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_WARNING,
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_SUCCESS,
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_INFO,
        ];
        $types = $request->get('types', []);
        if ($types) {
            $types = array_keys($types);
            foreach ($types as $key => $typeName) {
                if (!\in_array($typeName, $possibleTypes)) {
                    unset($types[$key]);
                }
            }
        }

        return $types;
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
