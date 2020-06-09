<?php

namespace Marello\Bundle\Magento2Bundle\Form\Type;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Url;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;

class TransportSettingFormType extends AbstractType
{
    public const ELEMENT_DATA_ROLE_WEBSITE_TO_CHANNEL_MAPPING = 'websiteToSalesChannelMapping';
    public const ELEMENT_DATA_ROLE_SALES_CHANNEL_GROUP = 'salesChannelGroup';

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'apiUrl',
            TextType::class,
            [
                'label' => 'marello.magento2.transport_setting_form.api_url.label',
                'required' => true,
                'constraints' => [
                    new Url()
                ]
            ]
        );

        $builder->add(
            'apiToken',
            OroEncodedPlaceholderPasswordType::class,
            [
                'label' => 'marello.magento2.transport_setting_form.api_token.label',
                'required' => true
            ]
        );

        $builder->add(
            'syncStartDate',
            OroDateType::class,
            [
                'label'      => 'marello.magento2.transport_setting_form.sync_start_date.label',
                'required'   => true,
                'tooltip'    => 'marello.magento2.transport_setting_form.sync_start_date.tooltip',
                'empty_data' => new \DateTime('2007-01-01', new \DateTimeZone('UTC'))
            ]
        );

        $builder->add(
            'deleteRemoteDataOnDeactivation',
            CheckboxType::class,
            [
                'label' => 'marello.magento2.transport_setting_form.delete_remote_data_on_deactivation.label',
                'required' => false
            ]
        );

        $builder->add(
            'deleteRemoteDataOnDeletion',
            CheckboxType::class,
            [
                'label' => 'marello.magento2.transport_setting_form.delete_remote_data_on_deletion.label',
                'required' => false
            ]
        );

        $builder->add(
            'check',
            TransportCheckButtonType::class,
            [
                'label' => 'marello.magento2.connection_validation.button.text',
                'salesGroupSelector' => sprintf('[data-role="%s"]', self::ELEMENT_DATA_ROLE_SALES_CHANNEL_GROUP),
                'websiteToSalesChannelMappingSelector' => sprintf(
                    '[data-role="%s"]', self::ELEMENT_DATA_ROLE_WEBSITE_TO_CHANNEL_MAPPING
                )
            ]
        );

        $builder->add(
            $builder
                ->create(
                    'websiteToSalesChannelMapping',
                    HiddenType::class,
                    [
                        'attr' => ['data-role' => self::ELEMENT_DATA_ROLE_WEBSITE_TO_CHANNEL_MAPPING],
                        'data' => [
                            [
                                'website_code' => 'MAG',
                                'sales_chanel_code' => 'MAR',
                            ],
                            [
                                'website_code' => 'MAG_2',
                                'sales_chanel_code' => 'MAR_2',
                            ]
                        ]
                    ]
                )
                ->addViewTransformer(new ArrayToJsonTransformer())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Magento2Transport::class]);
    }
}
