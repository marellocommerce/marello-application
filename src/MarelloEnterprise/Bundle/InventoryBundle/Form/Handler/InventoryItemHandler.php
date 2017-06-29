<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Handler;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryItemHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param FormInterface $form
     * @param ObjectManager $manager
     * @param Request $request
     */
    public function __construct(
        FormInterface $form,
        ObjectManager $manager,
        Request $request
    ) {
        $this->form = $form;
        $this->manager = $manager;
        $this->request = $request;
    }

    /**
     * @param InventoryItem $entity
     * @return bool
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
     * @return \Symfony\Component\Form\FormView
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
