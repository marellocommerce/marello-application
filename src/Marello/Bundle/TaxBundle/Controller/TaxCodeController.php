<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class TaxCodeController
 * @package Marello\Bundle\TaxBundle\Controller
 */
class TaxCodeController extends Controller
{
    /**
     * @Config\Route("/", name="marello_tax_taxcode_index")
     * @Config\Template
     * @AclAncestor("marello_tax_taxcode_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloTaxBundle:TaxCode'];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_tax_taxcode_view")
     * @Config\Template
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
     * @Config\Route("/create", name="marello_tax_taxcode_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
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
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_tax_taxcode_update")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
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
