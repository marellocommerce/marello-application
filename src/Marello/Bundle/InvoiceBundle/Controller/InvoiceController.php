<?php

namespace Marello\Bundle\InvoiceBundle\Controller;

use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InvoiceController extends Controller
{
    /**
     * @Config\Route("/", name="marello_invoice_invoice_index")
     * @Config\Template
     * @AclAncestor("marello_invoice_view")
     */
    public function indexAction()
    {
        return ['entity_class' => AbstractInvoice::class];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_invoice_invoice_view")
     * @Config\Template
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
