<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReturnItemType extends AbstractType
{
    const NAME = 'marello_return_item';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', 'number', [
            'data' => 0,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\ReturnBundle\Entity\ReturnItem',
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }
}
