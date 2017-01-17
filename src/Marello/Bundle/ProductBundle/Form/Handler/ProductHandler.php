<?php

namespace Marello\Bundle\ProductBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SupplierBundle\Entity\ProductSupplierRelation;
use Oro\Component\Layout\ArrayCollection;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /**
     * @param FormInterface   $form
     * @param Request         $request
     * @param ObjectManager   $manager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager
    ) {
        $this->form            = $form;
        $this->request         = $request;
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
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $addChannels = $this->form->get('addSalesChannels')->getData();
                $removeChannels = $this->form->get('removeSalesChannels')->getData();
                $suppliers = $this->form->get('suppliers')->getData();

                $this->onSuccess($entity, $addChannels, $removeChannels, $suppliers);

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
     * @param ArrayCollection $suppliers
     */
    protected function onSuccess(Product $entity, array $addChannels, array $removeChannels, $suppliers)
    {
        $this->addChannels($entity, $addChannels);
        $this->removeChannels($entity, $removeChannels);
        $this->setProductSupplierRelationProduct($entity, $suppliers);

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
     * @param ArrayCollection $suppliers
     */
    protected function setProductSupplierRelationProduct(Product $product, $suppliers)
    {
        //todo sets Product NULL when edit

        /** @var $supplier ProductSupplierRelation */
        foreach ($suppliers as $supplier) {
            $supplier->setProduct($product);
        }
    }
}
