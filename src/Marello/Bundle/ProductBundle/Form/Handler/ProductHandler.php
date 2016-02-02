<?php

namespace Marello\Bundle\ProductBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Events\InventoryLogEvent;
use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * @param FormInterface            $form
     * @param Request                  $request
     * @param ObjectManager            $manager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->form       = $form;
        $this->request    = $request;
        $this->manager    = $manager;
        $this->dispatcher = $dispatcher;
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
        /** @var FormInterface $item */
        foreach ($this->form->get('inventoryItems') as $item) {
            $operator = $item->get('modifyOperator')->getData();
            $amount   = $item->get('modifyAmount')->getData();

            if ($amount === 0) {
                continue;
            }

            if ($operator === InventoryItem::MODIFY_OPERATOR_DECREASE) {
                $amount *= -1;
            }

            $this->dispatcher->dispatch(InventoryLogEvent::NAME, new InventoryLogEvent(
                $item->getData(),
                $item->getData()->getQuantity() - $amount,
                $item->getData()->getQuantity(),
                'manual'
            ));
        }

        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
