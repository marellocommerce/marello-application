<?php

namespace Marello\Bundle\RefundBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Form\Type\RefundType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class RefundController extends Controller
{
    /**
     * @Config\Route("/", name="marello_refund_index")
     * @Config\Template
     * @AclAncestor("marello_refund_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => Refund::class,
        ];
    }

    /**
     * @Config\Route("/view/{id}", name="marello_refund_view")
     * @Config\Template
     * @AclAncestor("marello_refund_view")
     *
     * @param Refund $entity
     *
     * @return array
     */
    public function viewAction(Refund $entity)
    {
        return compact('entity');
    }

    /**
     * @Config\Route("/create/{id}", name="marello_refund_create")
     * @Config\Template("MarelloRefundBundle:Refund:update.html.twig")
     * @AclAncestor("marello_refund_create")
     *
     * @param Request $request
     * @param Order   $order
     *
     * @return array
     */
    public function createAction(Request $request, Order $order)
    {
        $entity = Refund::fromOrder($order);

        return $this->update($request, $entity);
    }


    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_refund_update")
     * @Config\Template
     * @AclAncestor("marello_refund_update")
     *
     * @param Request $request
     * @param Refund  $refund
     *
     * @return array
     */
    public function updateAction(Request $request, Refund $refund = null)
    {
        return $this->update($request, $refund);
    }

    /**
     * @param Request     $request
     * @param Refund|null $entity
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function update(Request $request, Refund $entity = null)
    {
        $form = $this->createForm(RefundType::NAME, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this
                ->getDoctrine()
                ->getManagerForClass(Refund::class);

            $manager->persist($entity = $form->getData());
            $manager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_refund_update',
                    'parameters' => [
                        'id' => $entity->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_refund_view',
                    'parameters' => [
                        'id' => $entity->getId(),
                    ],
                ],
                $entity
            );
        }

        $form = $form->createView();

        return compact('form', 'entity');
    }
}
