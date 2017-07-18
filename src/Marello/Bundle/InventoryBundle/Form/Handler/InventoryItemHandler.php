<?php

namespace Marello\Bundle\InventoryBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryItemHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  InventoryItem $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(InventoryItem $entity)
    {
        $this->form->setData($entity);

        if ($this->request->isMethod('POST')) {
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
     * @return FormView
     */
    public function getFormView()
    {
        return $this->form->createView();
    }

    /**
     * "Success" form handler
     *
     * @param InventoryItem $entity
     */
    protected function onSuccess(InventoryItem $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
