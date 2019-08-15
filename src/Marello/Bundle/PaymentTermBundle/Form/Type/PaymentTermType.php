<?php

namespace Marello\Bundle\PaymentTermBundle\Form\Type;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @inheritDoc
 */
class PaymentTermType extends AbstractType
{
    const BLOCK_PREFIX = 'payment_term';

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'marello.paymentterm.code.label',
                'required' => true,
            ])
            ->add('term', IntegerType::class, [
                'label' => 'marello.paymentterm.term.label',
                'required' => true,
            ])
            ->add('labels', LocalizedFallbackValueCollectionType::class, [
                'label' => 'marello.paymentterm.labels.label',
                'required' => true,
                'entry_options' => ['constraints' => [new NotBlank()]],
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => PaymentTerm::class,
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
