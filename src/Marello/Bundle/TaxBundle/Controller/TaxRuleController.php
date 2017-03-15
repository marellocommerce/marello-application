<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation as Security;

use Marello\Bundle\TaxBundle\Entity\TaxRule;

/**
 * Class TaxRuleController
 * @package Marello\Bundle\TaxBundle\Controller
 * @Config\Route("/rule")
 */
class TaxRuleController extends Controller
{
    /**
     * @Config\Route("/", name="marello_tax_taxrule_index")
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxrule_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloTaxBundle:TaxRule'];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"})
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxrule_view")
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
     * @Config\Route("/create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxrule_create")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new TaxRule());
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"})
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxrule_update")
     *
     * @param Request $request
     * @param TaxRule   $taxRule
     *
     * @return array
     */
    public function updateAction(Request $request, TaxRule $taxRule)
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
            
            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_tax_taxrule_update',
                    'parameters' => [
                        'id' => $taxRule->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_tax_taxrule_view',
                    'parameters' => [
                        'id' => $taxRule->getId(),
                    ],
                ],
                $taxRule
            );
        }

        return [
            'entity' => $taxRule,
            'form'   => $handler->getFormView(),
        ];
    }
    
}
