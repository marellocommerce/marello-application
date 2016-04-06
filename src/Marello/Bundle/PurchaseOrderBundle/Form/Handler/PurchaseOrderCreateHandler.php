<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
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
     * @param array|int[] $products       Array of product ids.
     * @param bool        $invertProducts Whether the selection ofr products should be inverted.
     *
     * @return bool
     */
    public function handle(array $products, $invertProducts)
    {
        $qb = $this->createProductsQueryBuilder($products, $invertProducts);

        $products = $qb->getQuery()->getResult();

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
     * @param array $productIds
     * @param bool  $invertSelection
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createProductsQueryBuilder(array $productIds, $invertSelection)
    {
        $qb = $this->doctrine->getRepository(Product::class)->createQueryBuilder('p');

        $qbs = $this->doctrine->getRepository(PurchaseOrderItem::class)->createQueryBuilder('poi');

        $qbs
            ->select('IDENTITY(poi.product)')
            ->join('poi.order', 'po')
            ->join('poi.workflowStep', 'ws')
            ->where($qbs->expr()->eq('ws.name', $qbs->expr()->literal('pending')));

        $qb
            ->select('p')
            ->join('p.inventoryItems', 'i')
            ->join('p.status', 's')
            ->having('SUM(i.quantity - i.allocatedQuantity) < p.purchaseStockLevel')
            ->andWhere($qb->expr()->eq('s.name', $qb->expr()->literal('enabled')))
            ->andWhere($qb->expr()->notIn('p.id', $qbs->getDQL()))
            ->groupBy('p.id');

        if (!empty($productIds)) {
            $qb->where(
                $invertSelection
                    ? $qb->expr()->notIn('p.id', $productIds)
                    : $qb->expr()->in('p.id', $productIds)
            );
        }

        return $qb;
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
