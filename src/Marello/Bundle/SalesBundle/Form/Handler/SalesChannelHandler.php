<?php

namespace Marello\Bundle\SalesBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class SalesChannelHandler
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
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  SalesChannel $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(SalesChannel $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
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
     * @param SalesChannel $entity
     */
    protected function onSuccess(SalesChannel $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
