<?php

namespace Marello\Bundle\TaxBundle\Form\Type;

use Marello\Bundle\TaxBundle\Entity\ZipCode;
use Marello\Bundle\TaxBundle\Form\DataTransformer\ZipCodeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ZipCodeType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_tax_zip_code_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ZipCodeTransformer());
        $builder
            ->add('zipRangeStart', TextType::class, [
                'required' => true,
            ])
            ->add('zipRangeEnd', TextType::class, [
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ZipCode::class,
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
