<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Form\Type\TaxJurisdictionType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TaxJurisdictionController
 * @package Marello\Bundle\TaxBundle\Controller
  */
class TaxJurisdictionController extends Controller
{
    /**
     * @Config\Route("/", name="marello_tax_taxjurisdiction_index")
     * @Config\Template
     * @AclAncestor("marello_tax_taxjurisdiction_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => 'MarelloTaxBundle:TaxJurisdiction'
        ];
    }

    /**
     * @Config\Route("/view/{id}", name="marello_tax_taxjurisdiction_view", requirements={"id"="\d+"})
     * @Config\Template
     * @Acl(
     *      id="marello_tax_taxjurisdiction_view",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxJurisdiction",
     *      permission="VIEW"
     * )
     *
     * @param TaxJurisdiction $taxJurisdiction
     * @return array
     */
    public function viewAction(TaxJurisdiction $taxJurisdiction)
    {
        return [
            'entity' => $taxJurisdiction
        ];
    }

    /**
     * @Config\Route("/create", name="marello_tax_taxjurisdiction_create")
     * @Config\Template("MarelloTaxBundle:TaxJurisdiction:update.html.twig")
     * @Acl(
     *      id="marello_tax_taxjurisdiction_create",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxJurisdiction",
     *      permission="CREATE"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new TaxJurisdiction(), $request);
    }

    /**
     * @Config\Route("/update/{id}", name="marello_tax_taxjurisdiction_update", requirements={"id"="\d+"})
     * @Config\Template
     * @Acl(
     *      id="marello_tax_taxjurisdiction_update",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxJurisdiction",
     *      permission="EDIT"
     * )
     *
     * @param Request $request
     * @param TaxJurisdiction $taxJurisdiction
     * @return array
     */
    public function updateAction(Request $request, TaxJurisdiction $taxJurisdiction)
    {
        return $this->update($taxJurisdiction, $request);
    }

    /**
     * @param TaxJurisdiction $taxJurisdiction
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(TaxJurisdiction $taxJurisdiction, Request $request)
    {
        return $this->get('oro_form.update_handler')->update(
            $taxJurisdiction,
            $this->createForm(TaxJurisdictionType::class, $taxJurisdiction),
            $this->get('translator')->trans('marello.tax.messages.success.taxjurisdiction.saved'),
            $request
        );
    }
}
