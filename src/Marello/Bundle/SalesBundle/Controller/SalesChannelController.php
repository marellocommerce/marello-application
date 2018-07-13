<?php

namespace Marello\Bundle\SalesBundle\Controller;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SalesChannelController extends Controller
{
    /**
     * @Config\Route("/", name="marello_sales_saleschannel_index")
     * @Config\Method("GET")
     * @Config\Template
     * @AclAncestor("marello_sales_saleschannel_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => SalesChannel::class,
        ];
    }

    /**
     * @Config\Route("/create", name="marello_sales_saleschannel_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloSalesBundle:SalesChannel:update.html.twig")
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
     * @Config\Route("/view/{id}", name="marello_sales_saleschannel_view", requirements={"id"="\d+"})
     * @Config\Template
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
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_sales_saleschannel_update")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
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
        return $this->get('oro_form.update_handler')->update(
            $channel,
            $this->createForm(SalesChannelType::class, $channel),
            $this->get('translator')->trans('marello.sales.saleschannel.messages.success.saved'),
            $request,
            'marello_sales.saleschannel_form.handler'
        );
    }
}
