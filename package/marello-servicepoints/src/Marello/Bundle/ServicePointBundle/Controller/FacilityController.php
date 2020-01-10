<?php

namespace Marello\Bundle\ServicePointBundle\Controller;

use Marello\Bundle\ServicePointBundle\Entity\Facility;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FacilityController extends Controller
{
    /**
     * @Route("/", name="marello_servicepoint_facility_index")
     * @Template
     * @Acl(
     *      id="marello_servicepoint_facility_index",
     *      type="entity",
     *      class="MarelloServicePointBundle:Facility",
     *      permission="VIEW"
     * )
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/create", name="marello_servicepoint_facility_create")
     * @Template("MarelloServicePointBundle:Facility:update.html.twig")
     * @Acl(
     *      id="marello_servicepoint_facility_create",
     *      type="entity",
     *      class="MarelloServicePointBundle:Facility",
     *      permission="CREATE"
     * )
     */
    public function createAction(Request $request)
    {
        return $this->update(new Facility());
    }

    /**
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="marello_servicepoint_facility_update")
     * @Template("MarelloServicePointBundle:Facility:update.html.twig")
     * @Acl(
     *      id="marello_servicepoint_facility_update",
     *      type="entity",
     *      class="MarelloServicePointBundle:Facility",
     *      permission="EDIT"
     * )
     */
    public function updateAction(Request $request, Facility $entity)
    {
        return $this->update($entity);
    }

    protected function update(Facility $entity)
    {
        $handler = $this->get('marello_servicepoint.form_handler.facility');

        if ($handler->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.servicepoint.ui.facility.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($entity);
        }

        return [
            'entity' => $entity,
            'form'   => $handler->getFormView(),
        ];
    }
}
