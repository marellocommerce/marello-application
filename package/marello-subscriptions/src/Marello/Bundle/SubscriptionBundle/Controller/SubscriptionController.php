<?php

namespace Marello\Bundle\SubscriptionBundle\Controller;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Marello\Bundle\SubscriptionBundle\Form\Type\SubscriptionType;
use Marello\Bundle\SubscriptionBundle\Form\Type\SubscriptionUpdateType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class SubscriptionController extends Controller
{
    /**
     * @Config\Route("/", name="marello_subscription_index")
     * @Config\Template
     * @AclAncestor("marello_subscription_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloSubscriptionBundle:Subscription'];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_subscription_view")
     * @Config\Template
     * @AclAncestor("marello_subscription_view")
     *
     * @param Subscription $subscription
     *
     * @return array
     */
    public function viewAction(Subscription $subscription)
    {
        return ['entity' => $subscription];
    }

    /**
     * @Config\Route("/create", name="marello_subscription_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @AclAncestor("marello_subscription_create")
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
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_subscription_update")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @AclAncestor("marello_subscription_update")
     *
     * @param Request $request
     * @param Subscription $subscription
     *
     * @return array
     */
    public function updateAction(Request $request, Subscription $subscription)
    {
        return $this->update($request, $subscription);
    }

    /**
     * Handles order updates and creation.
     *
     * @param Request $request
     * @param Subscription $subscription
     *
     * @return array
     */
    protected function update(Request $request, Subscription $subscription = null)
    {
        $formClass = $subscription ? SubscriptionUpdateType::class : SubscriptionType::class;

        if ($subscription === null) {
            $subscription = new Subscription();
        }

        $form = $this->createForm($formClass, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.order.messages.success.order.saved')
            );
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($subscription);
            $manager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_subscription_update',
                    'parameters' => [
                        'id' => $subscription->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_subscription_view',
                    'parameters' => [
                        'id' => $subscription->getId(),
                    ],
                ],
                $subscription
            );
        }

        return [
            'entity' => $subscription,
            'form'   => $form->createView(),
        ];
    }

    /**
     * @Config\Route(
     *     "/widget/address/{id}/{typeId}",
     *     requirements={"id"="\d+","typeId"="\d+"},
     *     name="marello_subscription_address"
     * )
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloSubscriptionBundle:Subscription/widget:address.html.twig")
     * @AclAncestor("marello_subscription_view")
     *
     * @param Request $request
     * @param MarelloAddress $address
     *
     * @return array
     */
    public function addressAction(Request $request, MarelloAddress $address)
    {
        return [
            'subscriptionAddress' => $address,
            'addressType' => ((int)$request->get('typeId') === 1) ? 'billing' : 'shipping'
        ];
    }
    
    /**
     * @Config\Route("/update/address/{id}", requirements={"id"="\d+"}, name="marello_subscription_updateaddress")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloSubscriptionBundle:Subscription:widget/updateAddress.html.twig")
     * @AclAncestor("marello_subscription_update")
     *
     * @param Request $request
     * @param MarelloAddress $address
     *
     * @return array
     */
    public function updateAddressAction(Request $request, MarelloAddress $address)
    {
        $responseData = array(
            'saved' => false
        );
        $form  = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['subscriptionAddress'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();
        return $responseData;
    }
}
