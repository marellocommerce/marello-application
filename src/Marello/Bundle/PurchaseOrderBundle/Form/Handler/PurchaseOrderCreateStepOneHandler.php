<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PurchaseOrderCreateStepOneHandler
{
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
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                return true;
            }
        }

        return false;
    }
}
