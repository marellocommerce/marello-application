<?php

namespace Marello\Bundle\PaymentBundle\Form\Type;

use Marello\Bundle\PaymentBundle\Provider\PaymentMethodChoicesProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_payment_method_select';
    
    /**
     * @var PaymentMethodChoicesProviderInterface
     */
    protected $paymentMethodChoicesProvider;

    /**
     * @param PaymentMethodChoicesProviderInterface $paymentMethodChoicesProvider
     */
    public function __construct(PaymentMethodChoicesProviderInterface $paymentMethodChoicesProvider)
    {
        $this->paymentMethodChoicesProvider = $paymentMethodChoicesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'expanded' => true,
                'choices' => array_flip($this->getChoices()),
            ]);
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        return $this->paymentMethodChoicesProvider->getMethods();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
