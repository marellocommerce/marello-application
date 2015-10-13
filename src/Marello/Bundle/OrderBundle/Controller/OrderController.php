<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Order;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OrderController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Template
     */
    public function indexAction()
    {
        return array('entity_class' => 'MarelloOrderBundle:Order');
    }

    /**
     * @Config\Route("/{id}", requirements={"id"="\d+"})
     * @Config\Template
     *
     * @param Order $order
     *
     * @return array
     */
    public function viewAction(Order $order)
    {
        return ['entity' => $order];
    }
}
