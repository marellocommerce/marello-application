<?php

namespace Marello\Bundle\ServicePointBundle\Controller;

use Marello\Bundle\ServicePointBundle\Entity\ServicePoint;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ServicePointController extends Controller
{
    /**
     * @Route("/", name="marello_servicepoint_servicepoint_index")
     * @Template
     * @AclAncestor("marello_servicepoint_servicepoint_view")
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/create", name="marello_servicepoint_servicepoint_create")
     * @Template("MarelloServicePointBundle:ServicePoint:update.html.twig")
     * @Acl(
     *      id="marello_servicepoint_servicepoint_create",
     *      type="entity",
     *      class="MarelloServicePointBundle:ServicePoint",
     *      permission="CREATE"
     * )
     */
    public function createAction(Request $request)
    {
        return $this->update(new ServicePoint());
    }

    /**
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="marello_servicepoint_servicepoint_update")
     * @Template("MarelloServicePointBundle:ServicePoint:update.html.twig")
     * @Acl(
     *      id="marello_servicepoint_servicepoint_update",
     *      type="entity",
     *      class="MarelloServicePointBundle:ServicePoint",
     *      permission="EDIT"
     * )
     */
    public function updateAction(Request $request, ServicePoint $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route("/view/{id}", requirements={"id" = "\d+"}, name="marello_servicepoint_servicepoint_view")
     * @Template
     * @Acl(
     *      id="marello_servicepoint_servicepoint_view",
     *      type="entity",
     *      class="MarelloServicePointBundle:ServicePoint",
     *      permission="VIEW"
     * )
     */
    public function viewAction(Request $request, ServicePoint $entity)
    {
        return [
            'entity' => $entity,
        ];
    }

    /**
     * @Route("/facilities/{id}", requirements={"id" = "\d+"}, name="marello_servicepoint_servicepoint_facilities")
     * @Template("MarelloServicePointBundle:ServicePoint:widget/facilities.html.twig")
     * @AclAncestor("marello_servicepoint_servicepoint_view")
     */
    public function facilitiesAction(Request $request, ServicePoint $entity)
    {
        return [
            'entity' => $entity,
        ];
    }

    protected function update(ServicePoint $entity)
    {
        $handler = $this->get('marello_servicepoint.form_handler.servicepoint');

        if ($handler->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.servicepoint.ui.servicepoint.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($entity);
        }

        return [
            'entity' => $entity,
            'form'   => $handler->getFormView(),
        ];
    }
}
