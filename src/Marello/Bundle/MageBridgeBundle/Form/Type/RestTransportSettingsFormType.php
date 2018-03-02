<?php

namespace Marello\Bundle\MageBridgeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RestTransportSettingsFormType extends AbstractType
{
    const NAME = 'marello_magebridge_rest_transport_setting_form_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $isExisting = $builder->getData() && $builder->getData()->getId();

        $builder->add(
            'url',
            'url',
            [
                'label' => 'marello.magebridge.magento.form.url.label',
                'required' => true,
                'tooltip' => 'marello.magebridge.magento.form.url.description',
            ]
        );

        $builder->add(
            'authenticate',
            'marello_magebrdige_transport_auth_button',
            [
                'label' => 'marello.magebridge.magento.transport.authenticate_connection.label'
            ]
        );


//        $builder->add(
//            'salesChannel',
//            'marello_sales_saleschannel_multi_select',
//            [
//                'label' => 'oro.magento.customer.data_channel.label',
////            'entities' => ['Marello\Bundle\SalesBundle\Entity'],
//                'required' => true,
//                'disabled' => $isExisting,
////            'single_channel_mode' => false
//            ]
//        );

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Marello\Bundle\MageBridgeBundle\Entity\MagentoRestTransport']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
