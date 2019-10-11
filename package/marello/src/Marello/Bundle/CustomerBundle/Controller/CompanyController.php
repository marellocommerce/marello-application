<?php

namespace Marello\Bundle\CustomerBundle\Controller;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CompanyController extends Controller
{
    /**
     * @Route("/", name="marello_customer_company_index")
     * @Template
     * @AclAncestor("marello_customer_company_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => Company::class
        ];
    }

    /**
     * @Route("/view/{id}", name="marello_customer_company_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="marello_customer_company_view",
     *      type="entity",
     *      class="MarelloCustomerBundle:Company",
     *      permission="VIEW"
     * )
     *
     * @param Company $company
     * @return array
     */
    public function viewAction(Company $company)
    {
        return [
            'entity' => $company,
            'treeData' => $this->get('marello_customer.company_tree_handler')->createTree($company),
        ];
    }

    /**
     * @Route("/create", name="marello_customer_company_create")
     * @Template("MarelloCustomerBundle:Company:update.html.twig")
     * @Acl(
     *      id="marello_customer_company_create",
     *      type="entity",
     *      class="MarelloCustomerBundle:Company",
     *      permission="CREATE"
     * )
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new Company());
    }

    /**
     * @Route("/update/{id}", name="marello_customer_company_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="marello_customer_company_update",
     *      type="entity",
     *      class="MarelloCustomerBundle:Company",
     *      permission="EDIT"
     * )
     *
     * @param Company $company
     * @return array
     */
    public function updateAction(Company $company)
    {
        return $this->update($company);
    }

    /**
     * @param Company $company
     * @return array|RedirectResponse
     */
    protected function update(Company $company)
    {
        $handler = $this->get('marello_customer.company.form.handler');

        if ($handler->process($company)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.customer.controller.company.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($company);
        }

        return [
            'entity' => $company,
            'form'   => $handler->getFormView(),
        ];
    }
}
