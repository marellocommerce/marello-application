<?php

namespace Marello\Bundle\CustomerBundle\Controller;

use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\CustomerBundle\Form\Handler\CustomerHandler;
use Oro\Bundle\ActivityListBundle\Entity\Manager\ActivityListManager;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerController extends AbstractController
{
    /**
     * @Route(path="/", name="marello_customer_index")
     * @Template
     * @AclAncestor("marello_customer_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return ['entity_class' => Customer::class];
    }

    /**
     * @Route(path="/view/{id}", requirements={"id"="\d+"}, name="marello_customer_view")
     * @Template
     * @AclAncestor("marello_customer_view")
     *
     * @param Customer $customer
     *
     * @return array
     */
    public function viewAction(Customer $customer)
    {
        $entityClass = $this->container->get(EntityRoutingHelper::class)->resolveEntityClass('marellocustomers');
        $manager = $this->container->get(ActivityListManager::class);
        $results = $manager->getListData(
            $entityClass,
            1000,
            [],
            []
        );
        
        return ['entity' => $customer];
    }

    /**
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_customer_create"
     * )
     * @Template("@MarelloCustomer/Customer/update.html.twig")
     * @AclAncestor("marello_customer_create")
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update();
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_customer_update"
     * )
     * @Template
     * @AclAncestor("marello_customer_update")
     *
     * @param Customer $customer
     *
     * @return array
     */
    public function updateAction(Customer $customer)
    {
        return $this->update($customer);
    }

    /**
     * @param Customer|null $customer
     *
     * @return mixed
     */
    private function update(Customer $customer = null)
    {
        if (!$customer) {
            $customer = new Customer();
        }

        return $this->container->get(UpdateHandler::class)->handleUpdate(
            $customer,
            $this->container->get(Form::class),
            function (Customer $entity) {
                return [
                    'route' => 'marello_customer_update',
                    'parameters' => ['id' => $entity->getId()]
                ];
            },
            function (Customer $entity) {
                return [
                    'route' => 'marello_customer_view',
                    'parameters' => ['id' => $entity->getId()]
                ];
            },
            $this->container->get(TranslatorInterface::class)->trans('marello.order.messages.success.customer.saved'),
            $this->container->get(CustomerHandler::class)
        );
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                EntityRoutingHelper::class,
                ActivityListManager::class,
                UpdateHandler::class,
                Form::class,
                TranslatorInterface::class,
                CustomerHandler::class,
            ]
        );
    }
}
