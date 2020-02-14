<?php

namespace Marello\Bundle\InvoiceBundle\Controller;

use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_invoice_invoice_index"
     * )
     * @Template
     * @AclAncestor("marello_invoice_view")
     */
    public function indexAction()
    {
        return ['entity_class' => AbstractInvoice::class];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_invoice_invoice_view"
     * )
     * @Template
     * @AclAncestor("marello_invoice_view")
     *
     * @param AbstractInvoice $invoice
     *
     * @return array
     */
    public function viewAction(AbstractInvoice $invoice)
    {
        return ['entity' => $invoice];
    }
}
