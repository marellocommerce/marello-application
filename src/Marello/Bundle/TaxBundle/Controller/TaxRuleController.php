<?php

namespace Marello\Bundle\TaxBundle\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Form\Handler\TaxRuleHandler;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update($request, new TaxRule());
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
     * @param Request $request
     * @return array
     */
    public function updateAction(TaxRule $taxRule, Request $request)
    {
        return $this->update($request, $taxRule);
    }

    /**
     * Handles supplier updates and creation.
     *
     * @param Request $request
     * @param TaxRule $taxRule
     * @return array
     */
    protected function update(Request $request, TaxRule $taxRule = null)
    {
        $handler = $this->container->get(TaxRuleHandler::class);
        
        if ($handler->process($taxRule)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.tax.messages.success.taxrule.saved')
            );
            
            return $this->container->get(Router::class)->redirect($taxRule);
        }

        return [
            'entity' => $taxRule,
            'form'   => $handler->getFormView(),
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TaxRuleHandler::class,
                TranslatorInterface::class,
                Router::class,
            ]
        );
    }
}
