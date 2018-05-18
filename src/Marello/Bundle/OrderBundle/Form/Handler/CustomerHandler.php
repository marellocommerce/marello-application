<?php

namespace Marello\Bundle\OrderBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerHandler
{
    /**
     * @var FormInterface
     */
    protected $form;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param EntityManagerInterface $manager
     */
    public function __construct(FormInterface $form, Request $request, EntityManagerInterface $manager)
    {
        $this->form = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * @param Customer $entity
     *
     * @return bool
     */
    public function process(Customer $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * @param Customer $entity
     */
    protected function onSuccess(Customer $entity)
    {
        $this->manager->persist($entity->getPrimaryAddress());
        if ($entity->getShippingAddress()) {
            $this->manager->persist($entity->getShippingAddress());
        }
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
