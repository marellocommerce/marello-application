<?php

namespace Marello\Bundle\OrderBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\SoapBundle\Controller\Api\FormAwareInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderApiHandler implements FormAwareInterface
{
    /**
     * @var FormInterface
     */
    protected $createForm;

    /**
     * @var FormInterface
     */
    protected $updateForm;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param FormInterface $createForm
     * @param FormInterface $updateForm
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(
        FormInterface $createForm,
        FormInterface $updateForm,
        Request $request,
        ObjectManager $manager
    ) {
        $this->createForm = $createForm;
        $this->updateForm = $updateForm;
        $this->request    = $request;
        $this->manager    = $manager;
    }

    /**
     * Process form
     *
     * @param  Order $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Order $entity)
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
     * @param Order $entity
     */
    protected function onSuccess(Order $entity)
    {
        $this->manager->persist($entity->getCustomer()->getPrimaryAddress());
        if ($entity->getCustomer()->getShippingAddress()) {
            $this->manager->persist($entity->getCustomer()->getShippingAddress());
        }
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->request->getMethod() === 'PUT' ? $this->updateForm : $this->createForm;
    }
}
