<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class TaxRuleController
 * @package Marello\Bundle\TaxBundle\Controller
 */
class TaxRuleController extends Controller
{
    /**
     * @Config\Route("/", name="marello_tax_taxrule_index")
     * @Config\Template
     * @AclAncestor("marello_tax_taxrule_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloTaxBundle:TaxRule'];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_tax_taxrule_view")
     * @Config\Template
     * @Acl(
     *      id="marello_tax_taxrule_view",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxRule",
     *      permission="VIEW"
     * )
     *
     * @param TaxRule $taxRule
     *
     * @return array
     */
    public function viewAction(TaxRule $taxRule)
    {
        return ['entity' => $taxRule];
    }

    /**
     * @Config\Route("/create", name="marello_tax_taxrule_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Acl(
     *      id="marello_tax_taxrule_create",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxRule",
     *      permission="CREATE"
     * )
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new TaxRule());
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_tax_taxrule_update")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Acl(
     *      id="marello_tax_taxrule_update",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxRule",
     *      permission="EDIT"
     * )
     *
     * @param TaxRule $taxRule
     *
     * @return array
     */
    public function updateAction(TaxRule $taxRule)
    {
        return $this->update($taxRule);
    }

    /**
     * Handles supplier updates and creation.
     *
     * @param TaxRule   $taxRule
     *
     * @return array
     */
    protected function update(TaxRule $taxRule = null)
    {
        $handler = $this->get('marello_tax.form.handler.taxrule');
        
        if ($handler->process($taxRule)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.tax.messages.success.taxrule.saved')
            );
            
            return $this->get('oro_ui.router')->redirect($taxRule);
        }

        return [
            'entity' => $taxRule,
            'form'   => $handler->getFormView(),
        ];
    }
}
