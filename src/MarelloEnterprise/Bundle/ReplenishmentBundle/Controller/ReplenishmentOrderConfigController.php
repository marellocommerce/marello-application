<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Controller;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReplenishmentOrderConfigController extends Controller
{
    /**
     * @Config\Route("/create", name="marello_replenishment_order_config_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
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
     * @Config\Route(
     *      "/widget/products/{id}",
     *      name="marello_replenishment_order_config_widget_products_candidates",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @AclAncestor("marello_product_view")
     * @Config\Template()
     */
    public function productsCandidatesAction()
    {
        return [];
    }
}
