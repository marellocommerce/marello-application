<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler;

use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ReplenishmentOrderStepOneHandler
{
    use RequestHandlerTrait;

    public function __construct(
        private FormInterface $form,
        private Request $request
    ) {}

    public function process(): bool
    {
        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);

            if ($this->form->isValid()) {
                return true;
            }
        }

        return false;
    }
}
