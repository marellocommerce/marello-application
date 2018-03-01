<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class TaxRateController
 * @package Marello\Bundle\TaxBundle\Controller
 * @Config\Route("/rate")
 */
class TaxRateController extends Controller
{
    /**
     * @Config\Route("/", name="marello_tax_taxrate_index")
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxrate_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloTaxBundle:TaxRate'];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_tax_taxrate_view")
     * @Config\Template
     * @Security\Acl(
     *      id="marello_tax_taxrate_view",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxRate",
     *      permission="VIEW"
     * )
     *
     * @param TaxRate $taxRate
     *
     * @return array
     */
    public function viewAction(TaxRate $taxRate)
    {
        return ['entity' => $taxRate];
    }

    /**
     * @Config\Route("/create", name="marello_tax_taxrate_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\Acl(
     *      id="marello_tax_taxrate_create",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxRate",
     *      permission="CREATE"
     * )
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new TaxRate());
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_tax_taxrate_update")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\Acl(
     *      id="marello_tax_taxrate_update",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxRate",
     *      permission="EDIT"
     * )
     *
     * @param TaxRate $taxRate
     *
     * @return array
     */
    public function updateAction(TaxRate $taxRate)
    {
        return $this->update($taxRate);
    }

    /**
     * Handles supplier updates and creation.
     *
     * @param TaxRate $taxRate
     *
     * @return array
     */
    protected function update(TaxRate $taxRate = null)
    {
        $handler = $this->get('marello_tax.form.handler.taxrate');
        
        if ($handler->process($taxRate)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.tax.messages.success.taxrate.saved')
            );
            
            return $this->get('oro_ui.router')->redirect($taxRate);
        }

        return [
            'entity' => $taxRate,
            'form'   => $handler->getFormView(),
        ];
    }
}
