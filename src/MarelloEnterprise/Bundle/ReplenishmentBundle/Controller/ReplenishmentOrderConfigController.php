<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Controller;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderConfigHandler;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler\ReplenishmentOrderStepOneHandler;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderConfigManualType;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderConfigType;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderStepOneType;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Model\ReplenishmentOrderStepOne;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\ManualReplenishmentStrategy;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReplenishmentOrderConfigController extends AbstractController
{
    /**
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_replenishment_order_config_create_step_one"
     * )
     * @Template("@MarelloEnterpriseReplenishment/ReplenishmentOrderConfig/createStepOne.html.twig")
     * @AclAncestor("marello_replenishment_order_config_create")
     */
    public function createAction(Request $request)
    {
        $model = new ReplenishmentOrderStepOne();
        $form = $this->createForm(ReplenishmentOrderStepOneType::class, $model);
        $handler = new ReplenishmentOrderStepOneHandler($form, $request);
        $queryParams = $request->query->all();

        if ($handler->process()) {
            $queryParams['type'] = $model->getType();

            return $this->redirect($this->generateUrl('marello_replenishment_order_config_create', $queryParams));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route(
     *     path="/create/step-two",
     *     name="marello_replenishment_order_config_create"
     * )
     * @Template("@MarelloEnterpriseReplenishment/ReplenishmentOrderConfig/create.html.twig")
     * @AclAncestor("marello_replenishment_order_config_create")
     */
    public function createStepTwoAction(Request $request)
    {
        $entity = new ReplenishmentOrderConfig();
        if ($request->query->get('type') === ReplenishmentOrderStepOne::MANUAL_TYPE) {
            $formType = ReplenishmentOrderConfigManualType::class;
            $entity->setStrategy(ManualReplenishmentStrategy::IDENTIFIER);
        } else {
            $formType = ReplenishmentOrderConfigType::class;
        }

        $form = $this->createForm($formType, $entity);
        $result = $this->container->get(ReplenishmentOrderConfigHandler::class)->process($form, $request);
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
            'entity' => $entity,
            'form' => $form->createView(),
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
