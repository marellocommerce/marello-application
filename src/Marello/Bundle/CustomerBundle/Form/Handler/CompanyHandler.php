<?php

namespace Marello\Bundle\CustomerBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CompanyHandler
{
    use RequestHandlerTrait;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EntityManager
     */
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
     * @param Company $company
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Company $company)
    {
        $this->form->setData($company);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);
            if ($this->form->isValid()) {
                /** @var FormInterface $appendCustomers */
                $appendCustomers = $this->form->get('appendCustomers');
                /** @var FormInterface $removeCustomers */
                $removeCustomers = $this->form->get('removeCustomers');
                $this->onSuccess($company, $appendCustomers->getData(), $removeCustomers->getData());

                return true;
            }
        }

        return false;
    }

    /**
     * @param Company $company
     * @param Customer[] $appendCustomers
     * @param Customer[] $removeCustomers
     */
    protected function onSuccess(Company $company, array $appendCustomers, array $removeCustomers)
    {
        $this->appendCustomers($company, $appendCustomers);
        $this->removeCustomers($company, $removeCustomers);

        $this->manager->persist($company);
        $this->manager->flush();
    }

    /**
     * @param Company $company
     * @param Customer[] $customers
     */
    protected function appendCustomers(Company $company, array $customers)
    {
        /** @var $customer Customer */
        foreach ($customers as $customer) {
            $company->addCustomer($customer);
        }
    }

    /**
     * @param Company $company
     * @param Customer[] $customers
     */
    protected function removeCustomers(Company $company, array $customers)
    {
        /** @var $customer Customer */
        foreach ($customers as $customer) {
            $company->removeCustomer($customer);
        }
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
}
