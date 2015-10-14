<?php

namespace Marello\Bundle\ProductBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Marello\Bundle\ProductBundle\Entity\Product;

class ProductHandler
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
     * @var ObjectManager
     */
    protected $manager;

    /**
     *
     * @param FormInterface $form
     * @param Request $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  Product $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(Product $entity)
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
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        $form = $this->form;

        $config = $form->getConfig();
        /** @var FormInterface $form */
        $form = $config->getFormFactory()->createNamed(
            $form->getName(),
            $config->getType()->getName(),
            $form->getData()
        );


        return $form->createView();
    }

    /**
     * "Success" form handler
     *
     * @param Product $entity
     */
    protected function onSuccess(Product $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
