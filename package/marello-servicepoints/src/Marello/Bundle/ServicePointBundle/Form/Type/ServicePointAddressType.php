<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\ServicePointAddress;
use Oro\Bundle\AddressBundle\Form\EventListener\AddressCountryAndRegionSubscriber;
use Oro\Bundle\AddressBundle\Form\Type\CountryType;
use Oro\Bundle\AddressBundle\Form\Type\RegionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServicePointAddressType extends AbstractType
{
    protected $countryAndRegionListener;

    public function __construct(AddressCountryAndRegionSubscriber $countryAndRegionListener)
    {
        $this->countryAndRegionListener = $countryAndRegionListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventSubscriber($this->countryAndRegionListener)
            ->add('organization', TextType::class, [
                'required' => false,
                'label' => 'oro.address.organization.label',
            ])
            ->add('country', CountryType::class, [
                'required' => true,
                'label' => 'oro.address.country.label',
            ])
            ->add('street', TextType::class, [
                'required' => false,
                'label' => 'oro.address.street.label',
            ])
            ->add('street2', TextType::class, [
                'required' => false,
                'label' => 'oro.address.street2.label',
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'label' => 'oro.address.city.label',
            ])
            ->add('region', RegionType::class, [
                'required' => false,
                'label' => 'oro.address.region.label',
            ])
            ->add('region_text', HiddenType::class, [
                'required' => false,
                'random_id' => true,
                'label' => 'oro.address.region_text.label',
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
                'label' => 'oro.address.postal_code.label',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServicePointAddress::class,
        ]);
    }
}
