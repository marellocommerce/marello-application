<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation as Security;

use Marello\Bundle\TaxBundle\Entity\TaxCode;

/**
 * Class TaxCodeController
 * @package Marello\Bundle\TaxBundle\Controller
 * @Config\Route("/codes")
 */
class TaxCodeController extends Controller
{
    /**
     * @Config\Route("/", name="marello_tax_taxcode_index")
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxcode_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloTaxBundle:TaxCode'];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"})
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxcode_view")
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
     * @Config\Route("/create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxcode_create")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new TaxCode());
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"})
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxcode_update")
     *
     * @param Request $request
     * @param TaxCode   $taxCode
     *
     * @return array
     */
    public function updateAction(Request $request, TaxCode $taxCode)
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
            
            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_tax_taxcode_update',
                    'parameters' => [
                        'id' => $taxCode->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_tax_taxcode_view',
                    'parameters' => [
                        'id' => $taxCode->getId(),
                    ],
                ],
                $taxCode
            );
        }

        return [
            'entity' => $taxCode,
            'form'   => $handler->getFormView(),
        ];
    }
    
}
