<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Marello\Bundle\TaxBundle\Form\Handler\TaxRateHandler;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TaxRateController
 * @package Marello\Bundle\TaxBundle\Controller
 */
class TaxRateController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_tax_taxrate_index"
     * )
     * @Template
     * @AclAncestor("marello_tax_taxrate_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloTaxBundle:TaxRate'];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_tax_taxrate_view"
     * )
     * @Template
     * @Acl(
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
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_tax_taxrate_create"
     * )
     * @Template
     * @Acl(
     *      id="marello_tax_taxrate_create",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxRate",
     *      permission="CREATE"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update($request, new TaxRate());
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_tax_taxrate_update"
     * )
     * @Template
     * @Acl(
     *      id="marello_tax_taxrate_update",
     *      type="entity",
     *      class="MarelloTaxBundle:TaxRate",
     *      permission="EDIT"
     * )
     *
     * @param TaxRate $taxRate
     * @param Request $request
     * @return array
     */
    public function updateAction(TaxRate $taxRate, Request $request)
    {
        return $this->update($request, $taxRate);
    }

    /**
     * Handles supplier updates and creation.
     *
     * @param Request $request
     * @param TaxRate $taxRate
     * @return array
     */
    protected function update(Request $request, TaxRate $taxRate = null)
    {
        $handler = $this->container->get(TaxRateHandler::class);
        
        if ($handler->process($taxRate)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.tax.messages.success.taxrate.saved')
            );
            
            return $this->container->get(Router::class)->redirect($taxRate);
        }

        return [
            'entity' => $taxRate,
            'form'   => $handler->getFormView(),
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TaxRateHandler::class,
                TranslatorInterface::class,
                Router::class,
            ]
        );
    }
}
