<?php

namespace Marello\Bundle\NotificationAlertBundle\Controller;

use Marello\Bundle\NotificationAlertBundle\Entity\NotificationAlert;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AjaxNotificationAlertController extends AbstractController
{
    /**
     * @Route(
     *      path="/resolve/{id}",
     *      methods={"POST"},
     *      name="marello_notificationalert_resolve",
     *      requirements={"id"="\d+"}
     * )
     * @CsrfProtection()
     * @AclAncestor("marello_notificationalert_update")
     *
     * @param NotificationAlert $entity
     * @return JsonResponse
     */
    public function resolveAction(NotificationAlert $entity)
    {
        try {
            $entityManager = $this->container->get('doctrine')->getManagerForClass(NotificationAlert::class);
            $className = ExtendHelper::buildEnumValueClassName(
                NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_ENUM_CODE
            );
            /** @var EnumValueRepository $enumRepo */
            $enumRepo = $entityManager->getRepository($className);
            $resolvedYes = $enumRepo->find(NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_YES);
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
