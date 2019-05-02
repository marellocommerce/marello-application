<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CustomerController extends Controller
{
    /**
     * @Config\Route("/", name="marello_order_customer_index")
     * @Config\Template
     * @AclAncestor("marello_customer_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return ['entity_class' => Customer::class];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_order_customer_view")
     * @Config\Template
     * @AclAncestor("marello_customer_view")
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
     * @Config\Route("/create", name="marello_order_customer_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("@MarelloOrder/Customer/update.html.twig")
     * @AclAncestor("marello_customer_create")
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
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_order_customer_update")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @AclAncestor("marello_customer_update")
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
     * @param Request $request
     * @param Customer|null $customer
     *
     * @return mixed
     */
    private function update(Request $request, Customer $customer = null)
    {
        if (!$customer) {
            $customer = new Customer();
        }

        return $this->get('oro_form.model.update_handler')
            ->handleUpdate(
                $customer,
                $this->get('marello_order.form.customer'),
                function (Customer $entity) {
                    return [
                        'route' => 'marello_order_customer_update',
                        'parameters' => ['id' => $entity->getId()]
                    ];
                },
                function (Customer $entity) {
                    return [
                        'route' => 'marello_order_customer_view',
                        'parameters' => ['id' => $entity->getId()]
                    ];
                },
                $this->get('translator')->trans('marello.order.messages.success.customer.saved'),
                $this->get('marello_order.form.handler.customer')
            );
    }
}
