<?php

namespace Marello\Bundle\ProductBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Variant;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductVariantHandler
{
    use RequestHandlerTrait;
    
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var Product
     */
    protected $parent;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param FormInterface            $form
     * @param RequestStack             $requestStack
     * @param ObjectManager            $manager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ObjectManager $manager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->form            = $form;
        $this->request         = $requestStack->getCurrentRequest();
        $this->manager         = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Process form
     *
     * @param Variant $entity
     * @param Product $parent
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Variant $entity, Product $parent)
    {
        $this->setParentEntity($entity, $parent);
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);

            if ($this->form->isValid()) {
                $addVariants = $this->form->get('addVariants')->getData();
                $removeVariants = $this->form->get('removeVariants')->getData();
                $this->onSuccess($entity, $addVariants, $removeVariants);

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
     * @param Variant $entity
     * @param array $addVariants
     * @param array $removeVariants
     */
    protected function onSuccess(Variant $entity, array $addVariants, array $removeVariants)
    {
        $this->addVariants($entity, $addVariants);
        $this->removeVariants($entity, $removeVariants);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Add channels to product
     *
     * @param Variant  $variant
     * @param Product[] $products
     */
    protected function addVariants(Variant $variant, array $products)
    {
        /** @var Product $product */
        foreach ($products as $product) {
            $variant->addProduct($product);
        }
    }

    /**
     * Remove channels from product
     *
     * @param Variant  $variant
     * @param Product[] $products
     */
    protected function removeVariants(Variant $variant, array $products)
    {
        /** @var Product $product */
        foreach ($products as $product) {
            $variant->removeProduct($product);
        }
    }

    /**
     * Set the parent product to the Variant entity
     *
     * @param Variant $entity
     * @param Product $parent
     */
    public function setParentEntity(Variant $entity, Product $parent)
    {
        $entity->addProduct($parent);
    }
}
