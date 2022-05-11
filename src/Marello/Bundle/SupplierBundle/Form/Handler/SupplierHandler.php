<?php

namespace Marello\Bundle\SupplierBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;

class SupplierHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

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

    /**
     * @param $data
     * @param FormInterface $form
     * @param Request $request
     * @return bool
     */
    public function process($data, FormInterface $form, Request $request)
    {
        if (!$data instanceof Supplier) {
            throw new \InvalidArgumentException('Argument data should be instance of Supplier entity');
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
     * @param Supplier $entity
     */
    protected function onSuccess(Supplier $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
