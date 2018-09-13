<?php

namespace Marello\Bundle\ReturnBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Form\Type\ReturnUpdateType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

class ReturnController extends Controller
{
    /**
     * @Config\Route("/", name="marello_return_return_index")
     * @Config\Template
     * @AclAncestor("marello_return_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloReturnBundle:ReturnEntity'];
    }

    /**
     * @Config\Route("/create/{id}", requirements={"id"="\d+"}, name="marello_return_return_create")
     * @Config\Template
     * @AclAncestor("marello_return_create")
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
        $return->setSalesChannel($order->getSalesChannel());

        if (null !== $order->getShipment()) {
            $form = $this->createForm('marello_return', $return);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $manager = $this->getDoctrine()->getManager();

                $manager->persist($return);
                $manager->flush();
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('marello.return.returnentity.messages.success.returnentity.saved')
                );
                return $this->get('oro_ui.router')->redirectAfterSave(
                    [
                        'route' => 'marello_return_return_update',
                        'parameters' => [
                            'id' => $return->getId(),
                            '_enableContentProviders' => 'mainMenu'
                        ]
                    ],
                    [
                        'route' => 'marello_return_return_view',
                        'parameters' => [
                            'id' => $return->getId(),
                            '_enableContentProviders' => 'mainMenu'
                        ]
                    ],
                    $return
                );
            }

            return [
                'form' => $form->createView(),
            ];
        } else {
            throw new ForbiddenException('An order without shipment cannot be returned');
        }
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_return_return_view")
     * @Config\Template
     * @AclAncestor("marello_return_view")
     *
     * @param ReturnEntity $return
     *
     * @return array
     */
    public function viewAction(ReturnEntity $return)
    {
        return ['entity' => $return];
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_return_return_update")
     * @Config\Template
     * @AclAncestor("marello_return_update")
     *
     * @param ReturnEntity $return
     * @param Request      $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(ReturnEntity $return, Request $request)
    {
        $form = $this->createForm(ReturnUpdateType::NAME, $return);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $manager->persist($return);
            $manager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_return_return_update',
                    'parameters' => [
                        'id' => $return->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_return_return_view',
                    'parameters' => [
                        'id'                      => $return->getId(),
                        '_enableContentProviders' => 'mainMenu'
                    ]
                ],
                $return
            );
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
