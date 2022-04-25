<?php

namespace Marello\Bundle\PaymentBundle\Provider\FormChanges;

use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\PaymentBundle\Form\Type\PaymentMethodSelectType;
use Marello\Bundle\PaymentBundle\Provider\PaymentMethodChoicesProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Twig\Environment;

class AvailablePaymentMethodsFormChangesProvider implements FormChangesProviderInterface
{
    const FIELD = 'paymentMethods';

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var PaymentMethodChoicesProviderInterface
     */
    protected $paymentMethodChoicesProvider;

    /**
     * @param Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param PaymentMethodChoicesProviderInterface $paymentMethodChoicesProvider
     */
    public function __construct(
        Environment $twig,
        FormFactoryInterface $formFactory,
        PaymentMethodChoicesProviderInterface $paymentMethodChoicesProvider
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->paymentMethodChoicesProvider = $paymentMethodChoicesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        if ($form->has('paymentMethod')) {
            $paymentFormName = $form->getName();
            $paymentSource = $form->get('paymentSource')->getData();
            if ($paymentSource instanceof AbstractInvoice) {
                $sourcePaymentMethod = $paymentSource->getPaymentMethod();
                if ($sourcePaymentMethod) {
                    $allPaymentMethods = $this->paymentMethodChoicesProvider->getMethods();
                    $choices = [$allPaymentMethods[$sourcePaymentMethod] => $sourcePaymentMethod];
                    $form = $this->formFactory
                        ->createNamedBuilder($paymentFormName)
                        ->add(
                            'paymentMethod',
                            PaymentMethodSelectType::class,
                            [
                                'label' => 'marello.payment.payment_method.label',
                                'required' => true,
                                'choices' => $choices,
                                'constraints' => new NotNull,
                            ]
                        )
                        ->getForm();
                }
            } else {
                $form = $this->formFactory
                    ->createNamedBuilder($paymentFormName)
                    ->add(
                        'paymentMethod',
                        PaymentMethodSelectType::class,
                        [
                            'label' => 'marello.payment.payment_method.label',
                            'required' => true,
                            'constraints' => new NotNull,
                        ]
                    )
                    ->getForm();
            }

            $result = $context->getResult();
            $result[self::FIELD] = $this->renderForm($form->createView());
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
            ->render('@MarelloPayment/Form/paymentMethodSelector.html.twig', ['form' => $formView]);
    }
}
