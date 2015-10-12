<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OrderController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Template()
     */
    public function indexAction()
    {
        return array('entity_class' => 'MarelloOrderBundle:Order');
    }
}
