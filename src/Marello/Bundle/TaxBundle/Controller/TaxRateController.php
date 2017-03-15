<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation as Security;

use Marello\Bundle\TaxBundle\Entity\TaxRate;

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
     * @Config\Route("/view/{id}", requirements={"id"="\d+"})
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxrate_view")
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
     * @Config\Route("/create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxrate_create")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new TaxRate());
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"})
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxrate_update")
     *
     * @param Request $request
     * @param TaxRate   $taxRate
     *
     * @return array
     */
    public function updateAction(Request $request, TaxRate $taxRate)
    {
        return $this->update($taxRate);
    }

    /**
     * Handles supplier updates and creation.
     *
     * @param TaxRate   $taxRate
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
            
            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_tax_taxrate_update',
                    'parameters' => [
                        'id' => $taxRate->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_tax_taxrate_view',
                    'parameters' => [
                        'id' => $taxRate->getId(),
                    ],
                ],
                $taxRate
            );
        }

        return [
            'entity' => $taxRate,
            'form'   => $handler->getFormView(),
        ];
    }
    
}
