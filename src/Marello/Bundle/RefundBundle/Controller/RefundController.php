<?php

namespace Marello\Bundle\RefundBundle\Controller;

use Marello\Bundle\RefundBundle\Entity\Refund;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;

class RefundController extends Controller
{
    /**
     * @Config\Route("/", name="marello_refund_index")
     * @Config\Template
     * @Security\AclAncestor("marello_refund_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => Refund::class,
        ];
    }

    /**
     * @Config\Route("/create", name="marello_refund_create")
     * @Config\Template
     * @Security\AclAncestor("marello_refund_create")
     */
    public function createAction(Request $request)
    {
        return [];
    }
}
