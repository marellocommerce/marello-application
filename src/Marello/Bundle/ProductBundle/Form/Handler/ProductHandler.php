<?php

namespace Marello\Bundle\ProductBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /**
     * @param FormInterface   $form
     * @param RequestStack    $requestStack
     * @param ObjectManager   $manager
     */
    public function __construct(
        FormInterface $form,
        RequestStack  $requestStack,
        ObjectManager $manager
    ) {
        $this->form            = $form;
        $this->request         = $requestStack->getCurrentRequest();
        $this->manager         = $manager;
    }

    /**
     * Process form
     *
     * @param  Product $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Product $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);

            if ($this->form->isValid()) {
                $addChannels = $this->form->get('addSalesChannels')->getData();
                $removeChannels = $this->form->get('removeSalesChannels')->getData();
                $salesChannelTaxCodes = $this->form->get('salesChannelTaxCodes')->getData();
                $suppliers = $this->form->get('suppliers')->getData();
                $appendCategories = $this->form->get('appendCategories')->getData();
                $removeCategories = $this->form->get('removeCategories')->getData();

                $this->onSuccess(
                    $entity,
                    $addChannels,
                    $removeChannels,
                    $salesChannelTaxCodes,
                    $suppliers,
                    $appendCategories,
                    $removeCategories
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        return $this->form->createView();
    }

    /**
     * "Success" form handler
     *
     * @param Product $entity
     * @param array $addChannels
     * @param array $removeChannels
     * @param ArrayCollection|ProductChannelTaxRelation[] $salesChannelTaxCodes
     * @param ArrayCollection|Supplier[] $suppliers
     * @param Category[] $appendCategories
     * @param Category[] $removeCategories
     */
    protected function onSuccess(
        Product $entity,
        array $addChannels,
        array $removeChannels,
        $salesChannelTaxCodes,
        $suppliers,
        array $appendCategories,
        array $removeCategories
    ) {
        $this->addChannels($entity, $addChannels);
        $this->removeChannels($entity, $removeChannels);
        $this->setSalesChannelTaxRelationProduct($entity, $salesChannelTaxCodes);
        $this->setProductSupplierRelationProduct($entity, $suppliers);
        $this->setPreferredSupplier($entity);
        $this->appendCategories($entity, $appendCategories);
        $this->removeCategories($entity, $removeCategories);
        $entity->updateDenormalizedProperties();

        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Add channels to product
     *
     * @param Product  $product
     * @param SalesChannel[] $channels
     */
    protected function addChannels(Product $product, array $channels)
    {
        /** @var $channel SalesChannel */
        foreach ($channels as $channel) {
            $product->addChannel($channel);
        }
    }

    /**
     * Remove channels from product
     *
     * @param Product  $product
     * @param SalesChannel[] $channels
     */
    protected function removeChannels(Product $product, array $channels)
    {
        /** @var $channels SalesChannel */
        foreach ($channels as $channel) {
            $product->removeChannel($channel);
        }
    }

    /**
     * @param Product $product
     * @param Supplier[] $suppliers
     */
    protected function setProductSupplierRelationProduct(Product $product, $suppliers)
    {
        /** @var $supplier ProductSupplierRelation */
        foreach ($suppliers as $supplier) {
            $supplier->setProduct($product);
        }
    }

    /**
     * @param Product $product
     * @param ProductChannelTaxRelation[] $salesChannelTaxCodes
     */
    protected function setSalesChannelTaxRelationProduct(Product $product, $salesChannelTaxCodes)
    {
        /** @var $supplier ProductChannelTaxRelation */
        foreach ($salesChannelTaxCodes as $salesChannelTaxCode) {
            $salesChannelTaxCode->setProduct($product);
        }
    }

    /**
     * @param Product $product
     */
    protected function setPreferredSupplier(Product $product)
    {
        $preferredSupplier = null;
        $preferredPriority = 0;
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            if (null == $preferredSupplier) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
                continue;
            }
            if ($productSupplierRelation->getPriority() < $preferredPriority) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
            }
        }

        if ($preferredSupplier) {
            $product->setPreferredSupplier($preferredSupplier);
        }
    }

    /**
     * @param Product $product
     * @param Category[] $categories
     */
    protected function appendCategories(Product $product, array $categories)
    {
        /** @var $category Category */
        foreach ($categories as $category) {
            $product->addCategory($category);
        }
    }

    /**
     * @param Product $product
     * @param Category[] $categories
     */
    protected function removeCategories(Product $product, array $categories)
    {
        /** @var $category Category */
        foreach ($categories as $category) {
            $product->removeCategory($category);
        }
    }
}
