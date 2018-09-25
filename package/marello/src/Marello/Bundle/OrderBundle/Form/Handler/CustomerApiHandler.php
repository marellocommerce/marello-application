<?php

namespace Marello\Bundle\OrderBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\SoapBundle\Controller\Api\FormAwareInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerApiHandler implements FormAwareInterface
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
     * @param Customer $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Customer $entity = null)
    {
        $form = $this->getForm();

        $form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $form->submit($this->request);

            if ($form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
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

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
