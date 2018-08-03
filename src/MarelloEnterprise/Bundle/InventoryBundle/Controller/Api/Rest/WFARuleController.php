<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller\Api\Rest;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\RouteResource("wfarule")
 * @Rest\NamePrefix("marelloenterprise_inventory_api_")
 */
class WFARuleController extends RestController implements ClassResourceInterface
{
    /**
     * Enable wfa rule
     *
     * Returns
     * - HTTP_OK (200)
     *
     * @Rest\Get(
     *      "/wfarules/{id}/enable",
     *      requirements={"version"="latest|v1"},
     *      defaults={"version"="latest", "_format"="json"}
     * )
     * @ApiDoc(description="Enable WFA Rule", resource=true)
     * @AclAncestor("marelloenterprise_inventory_wfa_rule_update")
     *
     * @param WFARule $wfaRule
     * @return Response
     */
    public function enableAction(WFARule $wfaRule)
    {
        return $this->changeStatus($wfaRule, 'marelloenterprise.inventory.wfarule.notification.enabled', true);
    }

    /**
     * Disable wfa rule
     *
     * Returns
     * - HTTP_OK (200)
     *
     * @Rest\Get(
     *      "/wfarules/{id}/disable",
     *      requirements={"version"="latest|v1"},
     *      defaults={"version"="latest", "_format"="json"}
     * )
     * @ApiDoc(description="Disable WFA Rule", resource=true)
     * @AclAncestor("marelloenterprise_inventory_wfa_rule_update")
     *
     * @param WFARule $wfaRule
     * @return Response
     */
    public function disableAction(WFARule $wfaRule)
    {
        return $this->changeStatus($wfaRule, 'marelloenterprise.inventory.wfarule.notification.disabled', false);
    }

    /**
     * Returns
     * - HTTP_OK (200)
     *
     * @Rest\Get(
     *      "/wfarules/{gridName}/massAction/{actionName}",
     *      requirements={"version"="latest|v1"},
     *      defaults={"version"="latest", "_format"="json"}
     * )
     * @AclAncestor("marelloenterprise_inventory_wfa_rule_update")
     *
     * @param string $gridName
     * @param string $actionName
     * @param Request $request
     * @return JsonResponse
     */
    public function massAction($gridName, $actionName, Request $request)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $request);

        $data = [
            'successful' => $response->isSuccessful(),
            'message' => $response->getMessage()
        ];

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }

    /**
     * @param WFARule $wfaRule
     * @param $message
     * @param $enabled
     * @return Response
     */
    protected function changeStatus(WFARule $wfaRule, $message, $enabled)
    {
        if ($wfaRule) {
            $wfaRule->getRule()->setEnabled($enabled);
            /** @var ObjectManager $objectManager */
            $objectManager = $this->getManager()->getObjectManager();
            $objectManager->persist($wfaRule);
            $objectManager->flush();
            $view = $this->view(
                [
                    'message'    =>
                        $this->get('translator')->trans($message),
                    'successful' => true,
                ],
                Codes::HTTP_OK
            );
        } else {
            /** @var View $view */
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        }


        return $this->handleView(
            $view
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('marelloenterprise_inventory.wfa_rule.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \LogicException('This method should not be called');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \LogicException('This method should not be called');
    }
}
