<?php

namespace Marello\Bundle\SalesBundle\Controller;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelType;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SalesChannelController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     methods={"GET"},
     *     name="marello_sales_saleschannel_index"
     * )
     * @Template
     * @AclAncestor("marello_sales_saleschannel_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => SalesChannel::class,
        ];
    }

    /**
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_sales_saleschannel_create"
     * )
     * @Template("@MarelloSales/SalesChannel/update.html.twig")
     * @AclAncestor("marello_saleschannel_create")
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new SalesChannel(), $request);
    }
    
    /**
     * @Route(
     *     path="/view/{id}",
     *     name="marello_sales_saleschannel_view",
     *     requirements={"id"="\d+"}
     * )
     * @Template
     * @Acl(
     *      id="marello_sales_saleschannel_view",
     *      type="entity",
     *      class="MarelloSalesBundle:SalesChannel",
     *      permission="VIEW"
     * )
     *
     * @param SalesChannel $salesChannel
     * @return array
     */
    public function viewAction(SalesChannel $salesChannel)
    {
        return [
            'entity' => $salesChannel,
        ];
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_sales_saleschannel_update"
     * )
     * @Template
     * @AclAncestor("marello_saleschannel_update")
     *
     * @param Request $request
     * @param SalesChannel $channel
     * @return array
     */
    public function updateAction(SalesChannel $channel, Request $request)
    {
        return $this->update($channel, $request);
    }

    /**
     * @param SalesChannel $channel
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(SalesChannel $channel, Request $request)
    {
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $channel,
            $this->createForm(SalesChannelType::class, $channel),
            $this->container->get(TranslatorInterface::class)->trans('marello.sales.saleschannel.messages.success.saved'),
            $request,
            'marello_sales.saleschannel_form.handler'
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
