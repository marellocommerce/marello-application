<?php

namespace Marello\Bundle\Magento2Bundle\Form\Type;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class TransportSettingFormType extends AbstractType
{
    private const BLOCK_PREFIX = 'marello_magento2_transport_setting';

    public const ELEMENT_DATA_ROLE_WEBSITE_TO_CHANNEL_MAPPING = 'websiteToSalesChannelMapping';
    public const ELEMENT_DATA_ROLE_SALES_CHANNEL_GROUP = 'salesChannelGroup';

    public const ELEMENT_DATA_ROLE_API_URL = 'apiUrl';
    public const ELEMENT_DATA_ROLE_API_TOKEN = 'apiToken';

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

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
                'tooltip'    => 'marello.magento2.transport_setting_form.api_url.tooltip',
                'constraints' => [
                    new Url(),
                    new NotBlank()
                ],
                'attr' => ['data-role' => TransportSettingFormType::ELEMENT_DATA_ROLE_API_URL]
            ]
        );

        $builder->add(
            'apiToken',
            OroEncodedPlaceholderPasswordType::class,
            [
                'label' => 'marello.magento2.transport_setting_form.api_token.label',
                'required' => true,
                'tooltip'    => 'marello.magento2.transport_setting_form.api_token.tooltip',
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => ['data-role' => TransportSettingFormType::ELEMENT_DATA_ROLE_API_TOKEN]
            ]
        );

        /**
         * @todo Schedule initial sync on changing this value
         */
        $builder->add(
            'initialSyncStartDate',
            OroDateType::class,
            [
                'label'      => 'marello.magento2.transport_setting_form.initial_sync_start_date.label',
                'required'   => true,
                'tooltip'    => 'marello.magento2.transport_setting_form.initial_sync_start_date.tooltip',
                'empty_data' => new \DateTime('2007-01-01', new \DateTimeZone('UTC')),
                'constraints' => [
                    new NotBlank()
                ]
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
                'selectorForFieldsRequiredReCheckConnection' => [
                    sprintf('[data-role="%s"]', self::ELEMENT_DATA_ROLE_API_URL)
                ]
            ]
        );

        $builder->add(
            'websiteToSalesChannelMappingControls',
            WebsiteToSalesChannelMappingControlsType::class,
            [
                'label' => 'Website To Sales Channel Mapping',
                'selectorSalesChannelGroup' => sprintf(
                    '[data-role="%s"]',
                    self::ELEMENT_DATA_ROLE_SALES_CHANNEL_GROUP
                ),
                'selectorWebsiteToSalesChannelMapping' => sprintf(
                    '[data-role="%s"]',
                    self::ELEMENT_DATA_ROLE_WEBSITE_TO_CHANNEL_MAPPING
                ),
                'mapped' => false
            ]
        );

        /**
         * @todo Schedule initial sync on changing this value
         */
        $builder->add(
            'websiteToSalesChannelMapping',
            WebsiteToSalesChannelMappingType::class,
            [
                'attr' => [
                    'data-role' => TransportSettingFormType::ELEMENT_DATA_ROLE_WEBSITE_TO_CHANNEL_MAPPING
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'marello.magento2.validator.not_empty_website_to_sales_channel_mapping'
                    ])
                ]
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Magento2Transport::class
        ]);
    }
}
