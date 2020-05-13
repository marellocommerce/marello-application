<?php

namespace Marello\Bundle\CustomerBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerHandler
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
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $manager
     */
    public function __construct(FormInterface $form, RequestStack $requestStack, EntityManagerInterface $manager)
    {
        $this->form = $form;
        $this->request = $requestStack->getCurrentRequest();
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
            $this->submitPostPutRequest($this->form, $this->request);

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
