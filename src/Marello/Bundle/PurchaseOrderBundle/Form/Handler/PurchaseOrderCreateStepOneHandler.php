<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Handler;

use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class PurchaseOrderCreateStepOneHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    private $form;

    /** @var Request */
    private $request;

    /**
     * Constructor.
     *
     * @param FormInterface         $form
     * @param Request               $request
     */
    public function __construct(
        FormInterface $form,
        Request $request
    ) {
        $this->form         = $form;
        $this->request     = $request;
    }

    /**
     * @return bool
     */
    public function process()
    {
        if ($this->request->isMethod(Request::METHOD_POST)) {
            $this->submitPostPutRequest($this->form, $this->request);

            if ($this->form->isValid()) {
                return true;
            }
        }

        return false;
    }
}
