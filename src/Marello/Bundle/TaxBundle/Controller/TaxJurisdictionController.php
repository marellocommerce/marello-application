<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Form\Type\TaxJurisdictionType;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class TaxJurisdictionController
 * @package Marello\Bundle\TaxBundle\Controller
 * @Config\Route("/jurisdiction")
 */
class TaxJurisdictionController extends Controller
{
    /**
     * @Config\Route("/", name="marello_tax_taxjurisdiction_index")
     * @Config\Template
     * @Security\AclAncestor("marello_tax_taxjurisdiction_view")
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
     * @Security\Acl(
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
     * @Security\Acl(
     *      id="marello_tax_taxjurisdiction_create",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxJurisdiction",
     *      permission="CREATE"
     * )
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new TaxJurisdiction());
    }

    /**
     * @Config\Route("/update/{id}", name="marello_tax_taxjurisdiction_update", requirements={"id"="\d+"})
     * @Config\Template
     * @Security\Acl(
     *      id="marello_tax_taxjurisdiction_update",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxJurisdiction",
     *      permission="EDIT"
     * )
     *
     * @param TaxJurisdiction $taxJurisdiction
     * @return array
     */
    public function updateAction(TaxJurisdiction $taxJurisdiction)
    {
        return $this->update($taxJurisdiction);
    }

    /**
     * @param TaxJurisdiction $taxJurisdiction
     * @return array|RedirectResponse
     */
    protected function update(TaxJurisdiction $taxJurisdiction)
    {
        return $this->get('oro_form.model.update_handler')->handleUpdate(
            $taxJurisdiction,
            $this->createForm(TaxJurisdictionType::NAME, $taxJurisdiction),
            function (TaxJurisdiction $taxJurisdiction) {
                return [
                    'route' => 'marello_tax_taxjurisdiction_update',
                    'parameters' => ['id' => $taxJurisdiction->getId()]
                ];
            },
            function (TaxJurisdiction $taxJurisdiction) {
                return [
                    'route' => 'marello_tax_taxjurisdiction_view',
                    'parameters' => ['id' => $taxJurisdiction->getId()]
                ];
            },
            $this->get('translator')->trans('marello.tax.messages.success.taxjurisdiction.saved')
        );
    }
}
