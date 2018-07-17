<?php

namespace Marello\Bundle\UPSBundle\Form\Type;

use Marello\Bundle\UPSBundle\Entity\Repository\ShippingServiceRepository;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Oro\Bundle\AddressBundle\Form\Type\CountryType;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class UPSTransportSettingsType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_ups_transport_settings';

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @param TransportInterface        $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'labels',
            LocalizedFallbackValueCollectionType::class,
            [
                'label' => 'marello.ups.transport.labels.label',
                'required' => true,
                'options' => ['constraints' => [new NotBlank()]],
            ]
        );
        $builder->add(
            'upsTestMode',
            CheckboxType::class,
            [
                'label' => 'marello.ups.transport.test_mode.label',
                'required' => false,
            ]
        );
        $builder->add(
            'upsApiUser',
            TextType::class,
            [
                'label' => 'marello.ups.transport.api_user.label',
                'required' => true
            ]
        );
        $builder->add(
            'upsApiPassword',
            OroEncodedPlaceholderPasswordType::class,
            [
                'label' => 'marello.ups.transport.api_password.label',
                'required' => true
            ]
        );
        $builder->add(
            'upsApiKey',
            TextType::class,
            [
                'label' => 'marello.ups.transport.api_key.label',
                'required' => true
            ]
        );
        $builder->add(
            'upsShippingAccountName',
            TextType::class,
            [
                'label' => 'marello.ups.transport.shipping_account_name.label',
                'required' => true
            ]
        );
        $builder->add(
            'upsShippingAccountNumber',
            TextType::class,
            [
                'label' => 'marello.ups.transport.shipping_account_number.label',
                'required' => true
            ]
        );
        $builder->add(
            'upsPickupType',
            ChoiceType::class,
            [
                'label' => 'marello.ups.transport.pickup_type.label',
                'required' => true,
                'choices' => [
                    UPSSettings::PICKUP_TYPE_REGULAR_DAILY => 'marello.ups.transport.pickup_type.regular_daily.label',
                    UPSSettings::PICKUP_TYPE_CUSTOMER_COUNTER =>
                        'marello.ups.transport.pickup_type.customer_counter.label',
                    UPSSettings::PICKUP_TYPE_ONE_TIME => 'marello.ups.transport.pickup_type.one_time.label',
                    UPSSettings::PICKUP_TYPE_ON_CALL_AIR => 'marello.ups.transport.pickup_type.on_call_air.label',
                    UPSSettings::PICKUP_TYPE_LETTER_CENTER => 'marello.ups.transport.pickup_type.letter_center.label',
                ]
            ]
        );
        $builder->add(
            'upsUnitOfWeight',
            ChoiceType::class,
            [
                'label' => 'marello.ups.transport.unit_of_weight.label',
                'required' => true,
                'choices' => [
                    UPSSettings::UNIT_OF_WEIGHT_LBS => 'marello.ups.transport.unit_of_weight.lbs.label',
                    UPSSettings::UNIT_OF_WEIGHT_KGS => 'marello.ups.transport.unit_of_weight.kgs.label'
                ]
            ]
        );
        $builder->add(
            'upsCountry',
            CountryType::class,
            [
                'label' => 'marello.ups.transport.country.label',
                'required' => true,
            ]
        );
        $builder->add(
            'applicableShippingServices',
            'entity',
            $this->getApplicableShippingServicesOptions()
        );
    }

    /**
     * @param FormEvent $event
     */
    protected function setApplicableShippingServicesChoicesByCountry(FormEvent $event)
    {
        /** @var UPSSettings $transport */
        $transport = $event->getData();
        $form = $event->getForm();

        if (!$transport) {
            return;
        }

        $country = $transport->getUpsCountry();

        $additionalOptions = [
            'choices' => [],
        ];
        if ($country) {
            $additionalOptions = [
                'query_builder' => function (ShippingServiceRepository $repository) use ($country) {
                    return $repository->createQueryBuilder('service')
                        ->where('service.country = :country')
                        ->setParameter('country', $country);
                },
            ];
        }

        $form->add('applicableShippingServices', 'entity', array_merge(
            $this->getApplicableShippingServicesOptions(),
            $additionalOptions
        ));
    }

    /**
     * @return array
     */
    protected function getApplicableShippingServicesOptions()
    {
        return [
            'label' => 'marello.ups.transport.shipping_service.plural_label',
            'required' => true,
            'multiple' => true,
            'class' => ShippingService::class,
        ];
    }

    /**
     * {@inheritdoc}
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass ?: $this->transport->getSettingsEntityFQCN()
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
