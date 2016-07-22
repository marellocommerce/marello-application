<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Form\Type\CustomerType;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Config\Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Template
     * @Security\AclAncestor("marello_customer_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return ['entity_class' => Customer::class];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"})
     * @Config\Template
     * @Security\AclAncestor("marello_customer_view")
     *
     * @param Customer $customer
     *
     * @return array
     */
    public function viewAction(Customer $customer)
    {
        return ['entity' => $customer];
    }

    /**
     * @Config\Route("/create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("@MarelloOrder/Customer/update.html.twig")
     * @Security\AclAncestor("marello_customer_create")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update($request);
    }

    /**
     * @Config\Route("/widget/create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("@MarelloOrder/Customer/widget/update.html.twig")
     * @Security\AclAncestor("marello_customer_create")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createWidgetAction(Request $request)
    {
        return $this->update($request);
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"})
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\AclAncestor("marello_customer_update")
     *
     * @param Request  $request
     * @param Customer $customer
     *
     * @return array
     */
    public function updateAction(Request $request, Customer $customer)
    {
        return $this->update($request, $customer);
    }

    /**
     * @param Request       $request
     * @param Customer|null $customer
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function update(Request $request, Customer $customer = null)
    {
        if (!$customer) {
            $customer = new Customer();
        }
        $form = $this->createForm(CustomerType::NAME, $customer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManagerForClass(Customer::class);

            $manager->persist($customer);
            $manager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_order_customer_update',
                    'parameters' => [
                        'id' => $customer->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_order_customer_view',
                    'parameters' => [
                        'id' => $customer->getId(),
                    ],
                ],
                $customer
            );
        }

        return [
            'entity' => $customer,
            'form'   => $form->createView(),
        ];
    }
}
