<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelGroupType extends AbstractType
{
    const NAME = 'marello_sales_channel_group';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        if ($entity instanceof SalesChannelGroup) {
            $entityId = $entity->getId();
        } else {
            $entityId = null;
        }

        $builder
            ->add(
                'name',
                TextType::class
            )
            ->add(
                'description',
                TextareaType::class
            )
            ->add(
                'salesChannels',
                SystemGroupSalesChannelMultiselectType::class,
                [
                    'attr' => [
                        'data-entity-id' => $entityId
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesChannelGroup::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
