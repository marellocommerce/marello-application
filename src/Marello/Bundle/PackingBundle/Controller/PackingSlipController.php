<?php

namespace Marello\Bundle\PackingBundle\Controller;

use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PackingSlipController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_packing_packingslip_index"
     * )
     * @Template("@MarelloPacking/PackingSlip/index.html.twig")
     * @AclAncestor("marello_packing_slip_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloPackingBundle:PackingSlip'];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_packing_packingslip_view"
     * )
     * @Template("@MarelloPacking/PackingSlip/view.html.twig")
     * @AclAncestor("marello_packing_slip_view")
     *
     * @param PackingSlip $packingSlip
     *
     * @return array
     */
    public function viewAction(PackingSlip $packingSlip)
    {
        return ['entity' => $packingSlip];
    }
}
