<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TaxCodeController
 * @package Marello\Bundle\TaxBundle\Controller
 */
class TaxCodeController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_tax_taxcode_index"
     * )
     * @Template
     * @AclAncestor("marello_tax_taxcode_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloTaxBundle:TaxCode'];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_tax_taxcode_view"
     * )
     * @Template
     * @Acl(
     *      id="marello_tax_taxcode_view",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxCode",
     *      permission="VIEW"
     * )
     *
     * @param TaxCode $taxCode
     *
     * @return array
     */
    public function viewAction(TaxCode $taxCode)
    {
        return ['entity' => $taxCode];
    }

    /**
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_tax_taxcode_create"
     * )
     * @Template
     * @Acl(
     *      id="marello_tax_taxcode_create",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxCode",
     *      permission="CREATE"
     * )
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new TaxCode());
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_tax_taxcode_update"
     * )
     * @Template
     * @Acl(
     *      id="marello_tax_taxcode_update",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxCode",
     *      permission="EDIT"
     * )
     *
     * @param TaxCode $taxCode
     *
     * @return array
     */
    public function updateAction(TaxCode $taxCode)
    {
        return $this->update($taxCode);
    }

    /**
     * Handles supplier updates and creation.
     *
     * @param TaxCode   $taxCode
     *
     * @return array
     */
    protected function update(TaxCode $taxCode = null)
    {
        $handler = $this->get('marello_tax.form.handler.taxcode');
        
        if ($handler->process($taxCode)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.tax.messages.success.taxcode.saved')
            );
            
            return $this->get('oro_ui.router')->redirect($taxCode);
        }

        return [
            'entity' => $taxCode,
            'form'   => $handler->getFormView(),
        ];
    }
}
