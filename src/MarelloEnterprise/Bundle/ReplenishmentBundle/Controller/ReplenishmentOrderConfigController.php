<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Controller;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReplenishmentOrderConfigController extends AbstractController
{
    /**
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_replenishment_order_config_create"
     * )
     * @Template("@MarelloEnterpriseReplenishment/ReplenishmentOrderConfig/create.html.twig")
     * @AclAncestor("marello_replenishment_order_config_create")
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update($request, new ReplenishmentOrderConfig());
    }

    /**
     * @param Request $request
     * @param ReplenishmentOrderConfig $orderConfig
     * @return RedirectResponse|array
     */
    protected function update(Request $request, ReplenishmentOrderConfig $orderConfig = null)
    {
        $handler = $this->container->get(ReplenishmentOrderConfigHandler::class);
        $result = $handler->process($orderConfig);
        if (isset($result['result']) && isset($result['messageType']) && isset($result['message']) &&
            $result['result'] !== false) {
            $request->getSession()->getFlashBag()->add(
                $result['messageType'],
                $this->container->get(TranslatorInterface::class)
                    ->trans($result['message'])
            );

            return $this->redirect($this->generateUrl('marelloenterprise_replenishmentorder_index'));
        }

        return [
            'entity' => $orderConfig,
            'form'   => $handler->getFormView(),
        ];
    }

    /**
     * @Route(
     *      path="/widget/products/{id}",
     *      name="marello_replenishment_order_config_widget_products_candidates",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @AclAncestor("marello_product_view")
     * @Template("@MarelloEnterpriseReplenishment/ReplenishmentOrderConfig/widget/productsCandidates.html.twig")
     */
    public function productsCandidatesAction()
    {
        return [];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ReplenishmentOrderConfigHandler::class,
                TranslatorInterface::class,
            ]
        );
    }
}
