<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Twig\Environment;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormFactoryInterface;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;

class OrderAddressFormChangesProvider implements FormChangesProviderInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @param Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param string $addressType
     */
    public function __construct(Environment $twig, FormFactoryInterface $formFactory, $addressType)
    {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->fieldName = sprintf('%sAddress', $addressType);
    }
    
    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        if ($form->has($this->fieldName)) {
            $orderFormName = $form->getName();
            $field = $form->get($this->fieldName);

            $form = $this->formFactory
                ->createNamedBuilder($orderFormName)
                ->add(
                    $this->fieldName,
                    get_class($field->getConfig()->getType()->getInnerType()),
                    $field->getConfig()->getOptions()
                )
                ->getForm();

            $form->submit($context->getSubmittedData());

            $result = $context->getResult();
            $result[$this->fieldName] = $this->renderForm($form->get($this->fieldName)->createView());
            $context->setResult($result);
        }
    }

    /**
     * @param FormView $formView
     * @return string
     */
    protected function renderForm(FormView $formView)
    {
        return $this
            ->twig
            ->render('@MarelloOrder/Form/customerAddressSelector.html.twig', ['form' => $formView]);
    }
}
