<?php

namespace Marello\Bundle\CustomerBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function process($data, FormInterface $form, Request $request)
    {
        if (!$data instanceof Customer) {
            throw new \InvalidArgumentException('Argument data should be instance of Customer entity');
        }

        $form->setData($data);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                $this->onSuccess($data);

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
