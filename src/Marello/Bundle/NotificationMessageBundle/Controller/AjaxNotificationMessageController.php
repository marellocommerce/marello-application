<?php

namespace Marello\Bundle\NotificationMessageBundle\Controller;

use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AjaxNotificationMessageController extends AbstractController
{
    /**
     * @Route(
     *      path="/resolve/{id}",
     *      methods={"POST"},
     *      name="marello_notificationmessage_resolve",
     *      requirements={"id"="\d+"}
     * )
     * @CsrfProtection()
     * @AclAncestor("marello_notificationmessage_update")
     *
     * @param NotificationMessage $entity
     * @return JsonResponse
     */
    public function resolveAction(NotificationMessage $entity)
    {
        try {
            $entityManager = $this->container->get('doctrine')->getManagerForClass(NotificationMessage::class);
            $className = ExtendHelper::buildEnumValueClassName(
                NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_ENUM_CODE
            );
            /** @var EnumValueRepository $enumRepo */
            $enumRepo = $entityManager->getRepository($className);
            $resolvedYes = $enumRepo->find(NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_YES);
            $entity->setResolved($resolvedYes);

            $entityManager->flush();
        } catch (\Throwable $exception) {
            return new JsonResponse(
                [
                    'successfull' => false,
                    'message' => $exception->getMessage(),
                ]
            );
        }

        return new JsonResponse(['successful' => true]);
    }
}
