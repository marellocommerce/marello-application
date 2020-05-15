<?php

namespace Marello\Bundle\Magento2Bundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransportCheckButtonType extends ButtonType
{
    public const NAME = 'marello_magento2_transport_check_button';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['websiteListSelector']);
        $resolver->setDefaults(['attr' => ['class' => 'btn btn-primary']]);
    }
}
