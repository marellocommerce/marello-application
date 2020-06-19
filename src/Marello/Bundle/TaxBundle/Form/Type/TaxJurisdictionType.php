<?php

namespace Marello\Bundle\TaxBundle\Form\Type;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Oro\Bundle\AddressBundle\Form\EventListener\AddressCountryAndRegionSubscriber;
use Oro\Bundle\AddressBundle\Form\Type\CountryType;
use Oro\Bundle\AddressBundle\Form\Type\RegionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxJurisdictionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_tax_jurisdiction_type';

    /**
     * @var AddressCountryAndRegionSubscriber
     */
    protected $countryAndRegionSubscriber;

    /**
     * @param AddressCountryAndRegionSubscriber $countryAndRegionSubscriber
     */
    public function __construct(AddressCountryAndRegionSubscriber $countryAndRegionSubscriber)
    {
        $this->countryAndRegionSubscriber = $countryAndRegionSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->countryAndRegionSubscriber);

        $builder
            ->add('code', TextType::class, [
                'label' => 'marello.tax.taxjurisdiction.code.label',
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'marello.tax.taxjurisdiction.description.label',
                'required' => false
            ])
            ->add('country', CountryType::class, [
                'required' => true,
                'label' => 'marello.tax.taxjurisdiction.country.label'
            ])
            ->add('region', RegionType::class, [
                'required' => false,
                'label' => 'marello.tax.taxjurisdiction.region.label'
            ])
            ->add('region_text', HiddenType::class, [
                'required' => false,
                'random_id' => true,
                'label' => 'marello.tax.taxjurisdiction.region_text.label'
            ])
            ->add('zipCodes', ZipCodeCollectionType::class, [
                'required' => false,
                'label' => 'marello.tax.taxjurisdiction.zip_codes.label',
                'tooltip'  => 'marello.tax.form.tooltip.zip_codes'
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TaxJurisdiction::class,
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
