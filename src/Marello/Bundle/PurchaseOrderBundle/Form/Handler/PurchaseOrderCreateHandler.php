<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PurchaseOrderCreateHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    private $form;

    /** @var ManagerRegistry */
    private $doctrine;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Request */
    private $request;

    /**
     * Constructor.
     *
     * @param FormInterface         $form
     * @param RequestStack          $requestStack
     * @param ManagerRegistry       $doctrine
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        FormInterface $form,
        RequestStack  $requestStack,
        ManagerRegistry $doctrine,
        TokenStorageInterface $tokenStorage
    ) {
        $this->form         = $form;
        $this->doctrine     = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->request      = $requestStack->getCurrentRequest();
    }

    /**
     *
     * @return bool
     */
    public function handle()
    {
        /*
         * Get organization of currently logged in user, or use first one.
         */
        $organization = null;
        if ($token = $this->tokenStorage->getToken()) {
            if ($token instanceof OrganizationAwareTokenInterface) {
                $organization = $token->getOrganization();
            }
        }
        if (!$organization) {
            $organization = $this->doctrine->getRepository(Organization::class)->getFirst();
        }

        $data = new PurchaseOrder();
        $data->setOrganization($organization);

        $this->form->setData($data);

        /*
         *  Unset any product key that do not need to be processed
         */
        $keys = $this->request->request->get('marello_purchase_order_create_step_two');
        if (!$keys) {
            return false;
        }

        $addedKeys = explode(',', $keys['itemsAdvice']['added']);
        if (key_exists('items', $keys)) {
            foreach ($keys['items'] as $key => $data) {
                if (null != $data['product'] && !in_array($data['product'], $addedKeys)) {
                    unset($keys['items'][$key]);
                }
            }
        }
        unset($keys['itemsAdvice']);
        $this->request->request->set('marello_purchase_order_create_step_two', $keys);

        $this->submitPostPutRequest($this->form, $this->request);

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            $this->onSuccess();

            return true;
        }

        return false;
    }

    /**
     * Saved data to database.
     */
    protected function onSuccess()
    {
        $data = $this->form->getData();

        $this
            ->doctrine
            ->getManagerForClass(PurchaseOrder::class)
            ->persist($data);

        $this
            ->doctrine
            ->getManagerForClass(PurchaseOrder::class)
            ->flush();
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
