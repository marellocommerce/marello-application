<?php

namespace Marello\Bundle\BankTransferBundle\Form\Type;

use Marello\Bundle\BankTransferBundle\Method\BankTransferMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankTransferOptionsType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_bank_transfer_options';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(BankTransferMethod::INSTRUCTIONS_OPTION, TextareaType::class, [
                'required' => true,
                'label' => 'marello.bank_transfer.method.instructions.label',
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'marello.bank_transfer.form.marello_bank_transfer_options_type.label',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
