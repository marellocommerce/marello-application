<?php

namespace Marello\Bundle\Magento2Bundle\Form\Type;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransportSettingFormType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'apiUrl',
            TextType::class,
            [
                'label' => '',
                'required' => true
            ]
        );

        $builder->add(
            'apiToken',
            TextType::class,
            [
                'label' => '',
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

        $websiteListDataRole = 'website-list';
        $builder->add(
            'check',
            TransportCheckButtonType::class,
            [
                'label' => 'marello.magento2.connection_validation.button.text',
                'websiteListSelector' => '[data-role="' . $websiteListDataRole . '"]'
            ]
        );

        $builder->add(
            $builder
                ->create(
                    'websites',
                    HiddenType::class,
                    [
                        'attr' => ['data-role' => $websiteListDataRole]
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
