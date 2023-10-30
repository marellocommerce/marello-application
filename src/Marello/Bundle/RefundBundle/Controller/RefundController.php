<?php

namespace Marello\Bundle\RefundBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Form\Type\RefundType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RefundController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_refund_index"
     * )
     * @Template
     * @AclAncestor("marello_refund_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => Refund::class,
        ];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     name="marello_refund_view"
     * )
     * @Template
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
     * @Route(
     *     path="/create/{id}",
     *     name="marello_refund_create"
     * )
     * @Template("@MarelloRefund/Refund/create.html.twig")
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
     * @Route(
     *     path="/update/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_refund_update"
     * )
     * @Template
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
        $form = $this->createForm(RefundType::class, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this
                ->container
                ->get(ManagerRegistry::class)
                ->getManagerForClass(Refund::class);

            $manager->persist($entity = $form->getData());
            $manager->flush();

            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.refund.messages.success.refund.saved')
            );

            return $this->container->get(Router::class)->redirect($entity);
        }

        $form = $form->createView();

        return compact('form', 'entity');
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                Router::class,
                ManagerRegistry::class,
            ]
        );
    }
}
