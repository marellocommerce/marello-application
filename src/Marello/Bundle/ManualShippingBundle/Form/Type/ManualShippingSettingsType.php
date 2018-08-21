<?php

namespace Marello\Bundle\ManualShippingBundle\Form\Type;

use Marello\Bundle\ManualShippingBundle\Entity\ManualShippingSettings;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ManualShippingSettingsType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_manual_shipping_settings';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'labels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label'    => 'marello.manual_shipping.settings.labels.label',
                    'required' => true,
                    'options'  => ['constraints' => [new NotBlank()]],
                ]
            );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ManualShippingSettings::class
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
