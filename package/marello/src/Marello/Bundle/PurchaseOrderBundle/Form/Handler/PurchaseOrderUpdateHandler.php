<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class PurchaseOrderUpdateHandler
{
    /** @var FormInterface */
    private $form;

    /** @var Request */
    private $request;

    /** @var EntityManager */
    private $entityManager;

    /**
     * Constructor.
     *
     * @param FormInterface         $form
     * @param Request               $request
     * @param EntityManager         $entityManager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        EntityManager $entityManager
    ) {
        $this->form             = $form;
        $this->request          = $request;
        $this->entityManager    = $entityManager;
    }

    /**
     *
     * @return bool
     */
    public function process(PurchaseOrder $entity)
    {
        $this->form->setData($entity);

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
        $this->entityManager->persist($this->form->getData());
        $this->entityManager->flush();
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
