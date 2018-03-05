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
            'infosUrl',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.infos_url.label',
                'required'  => true,
                'tooltip'   => 'marello.magebridge.magento.form.infos_url.description',
            ]
        );

        $builder->add(
            'clientId',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.client_id.label',
                'required'  => true,
                'tooltip'   => 'marello.magebridge.magento.form.client_id.description',
            ]
        );

        $builder->add(
            'clientSecret',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.client_secret.label',
                'required'  => true,
                'tooltip'   => 'marello.magebridge.magento.form.client_secret.description',
            ]
        );

        $builder->add(
            'authenticate',
            'marello_magebrdige_transport_auth_button',
            [
                'label'     => 'marello.magebridge.magento.transport.authenticate_connection.label',
            ]
        );

        //TODO : remove these fields they are automatically being filled in
        $builder->add(
            'token',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.token.label',
                'required'  => true,
                'disabled'  => true,
                'tooltip'   => 'marello.magebridge.magento.form.token.description',
            ]
        );

        $builder->add(
            'tokenSecret',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.token_secret.label',
                'required'  => true,
                'disabled'  => true,
                'tooltip'   => 'marello.magebridge.magento.form.token_secret.description',
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
