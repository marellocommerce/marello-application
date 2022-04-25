<?php

namespace Marello\Bundle\NotificationBundle\Controller;

use Marello\Bundle\NotificationBundle\Entity\Notification;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    /**
     * @Route(
     *     path="/view/thread/{id}",
     *     name="marello_notification_thread_view",
     *     requirements={"id"="\d+"}
     * )
     * @Template("@MarelloNotification/Notification/Thread/notificationItem.html.twig")
     * @AclAncestor("marello_notification_notification_view")
     * @param Notification $entity
     * @return array
     */
    public function viewThreadAction(Notification $entity)
    {
        return ['entity' => $entity];
    }
}
