<?php

namespace Marello\Bundle\SalesBundle\Controller;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelGroupType;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class SalesChannelGroupController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_sales_saleschannelgroup_index"
     * )
     * @Template
     * @AclAncestor("marello_sales_saleschannelgroup_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => SalesChannelGroup::class
        ];
    }

    /**
     * @Route(
     *     path="/create",
     *     name="marello_sales_saleschannelgroup_create"
     * )
     * @Template("@MarelloSales/SalesChannelGroup/update.html.twig")
     * @Acl(
     *     id="marello_sales_saleschannelgroup_create",
     *     type="entity",
     *     permission="CREATE",
     *     class="MarelloSalesBundle:SalesChannelGroup"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new SalesChannelGroup(), $request);
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     name="marello_sales_saleschannelgroup_view",
     *     requirements={"id"="\d+"}
     * )
     * @Template
     * @Acl(
     *      id="marello_sales_saleschannelgroup_view",
     *      type="entity",
     *      class="MarelloSalesBundle:SalesChannelGroup",
     *      permission="VIEW"
     * )
     *
     * @param SalesChannelGroup $salesChannelGroup
     *
     * @return array
     */
    public function viewAction(SalesChannelGroup $salesChannelGroup)
    {
        return [
            'entity' => $salesChannelGroup,
        ];
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     name="marello_sales_saleschannelgroup_update",
     *     requirements={"id"="\d+"}
     * )
     * @Template
     * @Acl(
     *     id="marello_sales_saleschannelgroup_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloSalesBundle:SalesChannelGroup"
     * )
     * @param Request $request
     * @param SalesChannelGroup $entity
     *
     * @return array
     */
    public function updateAction(Request $request, SalesChannelGroup $entity)
    {
        if ($entity->isSystem()) {
            $this->addFlash(
                'warning',
                'marello.sales.saleschannelgroup.messages.warning.is_system_update_attempt'
            );

            return $this->redirect($this->generateUrl('marello_sales_saleschannelgroup_index'));
        }

        return $this->update($entity, $request);
    }

    /**
     * @param SalesChannelGroup $entity
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(SalesChannelGroup $entity, Request $request)
    {
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->createForm(SalesChannelGroupType::class, $entity),
            $this->container->get(TranslatorInterface::class)->trans('marello.sales.saleschannelgroup.messages.success.saved'),
            $request,
            'marello_sales.saleschannelgroup_form.handler'
        );
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                UpdateHandlerFacade::class,
                TranslatorInterface::class,
            ]
        );
    }
}
