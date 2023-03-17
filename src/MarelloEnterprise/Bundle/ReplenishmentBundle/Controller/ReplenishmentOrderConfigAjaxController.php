<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Controller;

use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\LayoutBundle\Provider\CompositeFormChangesProvider;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type\ReplenishmentOrderConfigManualType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReplenishmentOrderConfigAjaxController extends AbstractController
{
    /**
     * @Route(
     *     path="/form-changes",
     *     methods={"POST"},
     *     name="marello_replenishment_form_changes"
     * )
     * @AclAncestor("marello_replenishment_order_config_create")
     */
    public function formChangesAction(Request $request)
    {
        $entity = new ReplenishmentOrderConfig();
        $form = $this->createForm(ReplenishmentOrderConfigManualType::class, $entity);
        $submittedData = $request->get($form->getName());
        $form->submit($submittedData);

        $context = new FormChangeContext(
            [
                FormChangeContext::FORM_FIELD => $form,
                FormChangeContext::SUBMITTED_DATA_FIELD => $submittedData,
                FormChangeContext::RESULT_FIELD => [],
            ]
        );

        $formChangesProvider = $this->container->get(CompositeFormChangesProvider::class);
        $formChangesProvider
            ->setRequiredDataClass(ReplenishmentOrderConfig::class)
            ->setRequiredFields(['manualItems'])
            ->processFormChanges($context);

        return new JsonResponse($context->getResult());
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                CompositeFormChangesProvider::class,
            ]
        );
    }
}
