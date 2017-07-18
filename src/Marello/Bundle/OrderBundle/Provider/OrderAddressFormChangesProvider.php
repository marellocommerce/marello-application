<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Templating\EngineInterface;

class OrderAddressFormChangesProvider implements FormChangesProviderInterface
{
    /**
     * @var EngineInterface
     */
    protected $templatingEngine;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @param EngineInterface $templatingEngine
     * @param FormFactoryInterface $formFactory
     * @param string $addressType
     */
    public function __construct(EngineInterface $templatingEngine, FormFactoryInterface $formFactory, $addressType)
    {
        $this->templatingEngine = $templatingEngine;
        $this->formFactory = $formFactory;
        $this->fieldName = sprintf('%sAddress', $addressType);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFormChangesData(FormInterface $form, array $submittedData = null)
    {
        if ($form->has($this->fieldName)) {
            $orderFormName = $form->getName();
            $field = $form->get($this->fieldName);

            $form = $this->formFactory
                ->createNamedBuilder($orderFormName)
                ->add(
                    $this->fieldName,
                    $field->getConfig()->getType()->getName(),
                    $field->getConfig()->getOptions()
                )
                ->getForm();

            $form->submit($submittedData);

            return $this->renderForm($form->get($this->fieldName)->createView());
        }
        
        return null;
    }

    /**
     * @param FormView $formView
     * @return string
     */
    protected function renderForm(FormView $formView)
    {
        return $this
            ->templatingEngine
            ->render('MarelloOrderBundle:Form:customerAddressSelector.html.twig', ['form' => $formView]);
    }
}
