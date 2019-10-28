<?php

namespace Marello\Bundle\ServicePointBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ServicePointFacilityFormHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var EntityManager */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param RequestStack  $requestStack
     * @param ObjectManager $manager
     */
    public function __construct(
        FormInterface $form,
        RequestStack  $requestStack,
        ObjectManager $manager
    ) {
        $this->form = $form;
        $this->request = $requestStack->getCurrentRequest();
        $this->manager = $manager;
    }

    /**
     * @param ServicePointFacility $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(ServicePointFacility $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);
            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * @param ServicePointFacility $entity
     */
    protected function onSuccess(ServicePointFacility $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Returns form instance
     *
     * @return FormView
     */
    public function getFormView()
    {
        return $this->form->createView();
    }
}
