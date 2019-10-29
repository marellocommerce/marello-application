<?php

namespace Marello\Bundle\ServicePointBundle\Controller;

use Marello\Bundle\ServicePointBundle\Entity\ServicePoint;
use Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ServicePointFacilityController extends Controller
{
    /**
     * @Route("/create/{servicepoint_id}", requirements={"servicepoint_id"="\d+"}, name="marello_servicepoint_servicepointfacility_create")
     * @ParamConverter("servicePoint", class="Marello\Bundle\ServicePointBundle\Entity\ServicePoint", options={"id" = "servicepoint_id"})
     * @Template("MarelloServicePointBundle:ServicePointFacility:update.html.twig")
     * @Acl(
     *      id="marello_servicepoint_servicepointfacility_create",
     *      type="entity",
     *      class="MarelloServicePointBundle:ServicePointFacility",
     *      permission="CREATE"
     * )
     */
    public function createAction(Request $request, ServicePoint $servicePoint)
    {
        $entity = new ServicePointFacility();
        $entity->setServicePoint($servicePoint);

        return $this->update($entity);
    }

    /**
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="marello_servicepoint_servicepointfacility_update")
     * @Template("MarelloServicePointBundle:ServicePointFacility:update.html.twig")
     * @Acl(
     *      id="marello_servicepoint_servicepointfacility_update",
     *      type="entity",
     *      class="MarelloServicePointBundle:ServicePointFacility",
     *      permission="EDIT"
     * )
     */
    public function updateAction(Request $request, ServicePointFacility $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route("/view/{id}", requirements={"id" = "\d+"}, name="marello_servicepoint_servicepointfacility_view")
     * @Template
     * @Acl(
     *      id="marello_servicepoint_servicepointfacility_view",
     *      type="entity",
     *      class="MarelloServicePointBundle:ServicePointFacility",
     *      permission="VIEW"
     * )
     */
    public function viewAction(Request $request, ServicePointFacility $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * @Route("/businesshours/{id}", requirements={"id" = "\d+"}, name="marello_servicepoint_servicepointfacility_businesshours")
     * @Template("MarelloServicePointBundle:ServicePointFacility:widget/businessHours.html.twig")
     * @AclAncestor("marello_servicepoint_servicepointfacility_view")
     */
    public function businesHoursAction(Request $request, ServicePointFacility $entity)
    {
        return ['entity' => $entity];
    }

    protected function update(ServicePointFacility $entity)
    {
        $handler = $this->get('marello_servicepoint.form_handler.servicepoint_facility');

        if ($handler->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.servicepoint.ui.servicepoint_facility.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($entity);
        }

        return [
            'entity' => $entity,
            'form'   => $handler->getFormView(),
        ];
    }
}
