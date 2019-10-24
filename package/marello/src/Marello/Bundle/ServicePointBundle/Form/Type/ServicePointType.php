<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\ServicePoint;
use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServicePointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('labels', LocalizedFallbackValueCollectionType::class, [
                'label' => 'marello.servicepoint.labels.label',
                'required' => true,
            ])
            ->add('descriptions', LocalizedFallbackValueCollectionType::class, [
                'label' => 'marello.servicepoint.descriptions.label',
                'required' => true,
            ])
            ->add('address', ServicePointAddressType::class, [
                'label' => 'marello.servicepoint.address.label',
                'required' => true,
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'marello.servicepoint.latitude.label',
                'required' => true,
                'scale' => 6,
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'marello.servicepoint.longitude.label',
                'required' => true,
                'scale' => 6,
            ])
            ->add('image', ImageType::class, [
                'label' => 'marello.servicepoint.image.label',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServicePoint::class,
        ]);
    }
}
