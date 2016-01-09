<?php

namespace Marello\Bundle\ReturnBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ReturnController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Template
     * @Security\AclAncestor("marello_return_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloReturnBundle:ReturnEntity'];
    }

    /**
     * @Config\Route("/create/{id}", requirements={"id"="\d+"})
     * @Config\Template
     * @Security\AclAncestor("marello_return_create")
     *
     * @param Order   $order
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Order $order, Request $request)
    {
        $return = new ReturnEntity();
        $return->setOrder($order);

        $form = $this->createForm('marello_return', $return);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $manager->persist($return);
            $manager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_return_return_view',
                    'parameters' => [
                        'id' => $order->getId(),
                    ],
                ],
                [
                    'route' => 'marello_return_return_index',
                ],
                $order
            );
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"})
     * @Config\Template
     * @Security\AclAncestor("marello_return_view")
     *
     * @param ReturnEntity $return
     *
     * @return array
     */
    public function viewAction(ReturnEntity $return)
    {
        return ['entity' => $return];
    }
}
