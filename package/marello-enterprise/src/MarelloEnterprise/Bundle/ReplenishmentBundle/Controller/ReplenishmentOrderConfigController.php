<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Controller;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReplenishmentOrderConfigController extends AbstractController
{
    /**
     * @Route(
     *     path="/create", 
     *     methods={"GET", "POST"},
     *     name="marello_replenishment_order_config_create"
     * )
     * @Template("MarelloEnterpriseReplenishmentBundle:ReplenishmentOrderConfig:create.html.twig")
     * @AclAncestor("marello_replenishment_order_config_create")
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new ReplenishmentOrderConfig());
    }

    /**
     * @param ReplenishmentOrderConfig $orderConfig
     *
     * @return RedirectResponse|array
     */
    protected function update(ReplenishmentOrderConfig $orderConfig = null)
    {
        $handler = $this->get('marelloenterprise_replenishment.form.handler.replenishment_order_config');
        $result = $handler->process($orderConfig);
        if (isset($result['result']) && isset($result['messageType']) && isset($result['message']) &&
            $result['result'] !== false) {
            $this->get('session')->getFlashBag()->add(
                $result['messageType'],
                $this->get('translator')
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
     * @Template("MarelloEnterpriseReplenishmentBundle:ReplenishmentOrderConfig/widget:productsCandidates.html.twig")
     */
    public function productsCandidatesAction()
    {
        return [];
    }
}
