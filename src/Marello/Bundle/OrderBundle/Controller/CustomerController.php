<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
}
