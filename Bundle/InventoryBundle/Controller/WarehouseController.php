<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Zend\Stdlib\Response;

/**
 * @Config\Route("/warehouse")
 */
class WarehouseController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Method("GET")
     * @Config\Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => 'Marello\Bundle\InventoryBundle\Entity\Warehouse',
        ];
    }

    /**
     * @Config\Route("/create")
     * @Config\Method({"GET", "POST"})
     *
     * @param Request   $request
     */
    public function createAction(Request $request)
    {
        // TODO: Create
    }

    /**
     * @Config\Route("/update/{id}")
     * @Config\Method({"GET", "POST"})
     *
     * @param Warehouse $warehouse
     * @param Request   $request
     */
    public function updateAction(Warehouse $warehouse, Request $request)
    {
        // TODO: Update
    }

    /**
     * @Config\Route("/delete/{id}")
     * @Config\Method("DELETE")
     *
     * @param Warehouse $warehouse
     *
     * @return Response
     */
    public function deleteAction(Warehouse $warehouse)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($warehouse);
        $em->flush();

        return new Response();
    }
}
