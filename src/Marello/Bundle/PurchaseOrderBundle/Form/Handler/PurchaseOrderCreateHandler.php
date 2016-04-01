<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PurchaseOrderCreateHandler
{
    /** @var FormInterface */
    private $form;

    /** @var Registry */
    private $doctrine;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Request */
    private $request;

    /**
     * Constructor.
     *
     * @param FormInterface         $form
     * @param Request               $request
     * @param Registry              $doctrine
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        Registry $doctrine,
        TokenStorageInterface $tokenStorage
    ) {
        $this->form         = $form;
        $this->doctrine     = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->request      = $request;
    }

    /**
     * @param array|int[] $products Array of product ids.
     *
     * @return bool
     */
    public function handle(array $products)
    {
        $pqb = $this->doctrine->getRepository(Product::class)->createQueryBuilder('p');

        $pqb
            ->select('p')
            ->join('p.inventoryItems', 'ii')
            ->where($pqb->expr()->in('p.id', $products));

        $products = $pqb->getQuery()->getResult();

        $organization = null;

        if ($token = $this->tokenStorage->getToken()) {
            $organization = $token->getOrganizationContext();
        } else {
            $organization = $this->doctrine->getRepository(Organization::class)->getFirst();
        }

        $data = PurchaseOrder::usingProducts($products, $organization);

        $this->form->setData($data);

        $this->form->handleRequest($this->request);

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
