<?php

namespace Marello\Bundle\InventoryBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Logging\InventoryLogger;
use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductInventoryHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /** @var InventoryLogger */
    protected $inventoryLogger;

    /**
     * @param FormInterface   $form
     * @param Request         $request
     * @param ObjectManager   $manager
     * @param InventoryLogger $inventoryLogger
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        InventoryLogger $inventoryLogger
    ) {
        $this->form            = $form;
        $this->request         = $request;
        $this->manager         = $manager;
        $this->inventoryLogger = $inventoryLogger;
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
                $this->onSuccess($entity);

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
     */
    protected function onSuccess(Product $entity)
    {
        $items = $entity->getInventoryItems()->toArray();

        foreach ($entity->getVariant()->getProducts() as $product) {
            $items = array_merge($items, $product->getInventoryItems()->toArray());
        }

        $this->inventoryLogger->log($items, 'manual');

        $this->manager->persist($entity->getVariant());
        $this->manager->flush();
    }
}
