<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PurchaseOrderCreateHandler
{
    /** @var FormInterface */
    private $form;

    /** @var Registry */
    private $doctrine;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param FormInterface         $form
     * @param Registry              $doctrine
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(FormInterface $form, Registry $doctrine, TokenStorageInterface $tokenStorage)
    {
        $this->form         = $form;
        $this->doctrine     = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param array|int[] $products Array of product ids.
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
            $organization = $token->getOrganization();
        } else {
            $organization = $this->doctrine->getRepository(Organization::class)->getFirst();
        }

        $data = PurchaseOrder::usingProducts($products, $organization);
    }
}
