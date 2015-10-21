<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')
            ->add('channelType')
            ->add('active');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\SalesBundle\Entity\SalesChannel',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'marello_sales_channel';
    }
}
