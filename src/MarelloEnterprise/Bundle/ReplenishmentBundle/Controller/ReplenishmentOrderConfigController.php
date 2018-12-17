<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Controller;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     * @return array
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

            return $this->redirect($this->generateUrl('marello_replenishment_order_index'));
        }

        return [
            'entity' => $orderConfig,
            'form'   => $handler->getFormView(),
        ];
    }
}
