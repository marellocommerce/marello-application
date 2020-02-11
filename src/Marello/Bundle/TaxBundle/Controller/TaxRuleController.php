<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TaxRuleController
 * @package Marello\Bundle\TaxBundle\Controller
 */
class TaxRuleController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_tax_taxrule_index"
     * )
     * @Template
     * @AclAncestor("marello_tax_taxrule_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloTaxBundle:TaxRule'];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_tax_taxrule_view"
     * )
     * @Template
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
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_tax_taxrule_create"
     * )
     * @Template
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
     * @Route(
     *     path="/update/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_tax_taxrule_update"
     * )
     * @Template
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
