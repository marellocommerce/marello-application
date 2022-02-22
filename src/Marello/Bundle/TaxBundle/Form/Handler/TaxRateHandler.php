<?php

namespace Marello\Bundle\TaxBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TaxRateHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /**
     * @param FormInterface   $form
     * @param RequestStack    $requestStack
     * @param ObjectManager   $manager
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ObjectManager $manager
    ) {
        $this->form            = $form;
        $this->request         = $requestStack->getCurrentRequest();
        $this->manager         = $manager;
    }

    /**
     * Process form
     *
     * @param  TaxRate $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(TaxRate $entity)
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
     * @param TaxRate $entity
     */
    protected function onSuccess(TaxRate $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
