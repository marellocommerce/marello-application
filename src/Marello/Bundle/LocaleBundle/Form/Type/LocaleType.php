<?php

namespace Marello\Bundle\LocaleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType as SymfonyLocaleType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_locale';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => array_flip(Intl::getLocaleBundle()->getLocaleNames('en')),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return SymfonyLocaleType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
