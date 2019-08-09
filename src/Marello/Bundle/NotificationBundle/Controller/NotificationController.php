<?php

namespace Marello\Bundle\NotificationBundle\Controller;

use Marello\Bundle\NotificationBundle\Entity\Notification;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{
    /**
     * @Config\Route("/view/thread/{id}", name="marello_notification_thread_view", requirements={"id"="\d+"})
     * @Config\Template("MarelloNotificationBundle:Notification/Thread:notificationItem.html.twig")
     * @AclAncestor("marello_notification_notification_view")
     * @param Notification $entity
     * @return array
     */
    public function viewThreadAction(Notification $entity)
    {
        return ['entity' => $entity];
    }
}
