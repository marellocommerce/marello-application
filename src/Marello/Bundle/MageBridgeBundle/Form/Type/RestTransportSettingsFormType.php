<?php

namespace Marello\Bundle\MageBridgeBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class RestTransportSettingsFormType extends AbstractType
{
    const NAME = 'marello_magebridge_rest_transport_setting_form_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isExisting = $builder->getData() && $builder->getData()->getId();

        $builder->add(
            'apiUrl',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.api_url.label',
                'tooltip'   => 'marello.magebridge.magento.form.api_url.description',
                'required'  => true,
                'constraints' => [new NotBlank()],
            ]
        );

        $builder->add(
            'adminUrl',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.admin_url.label',
                'tooltip'   => 'marello.magebridge.magento.form.admin_url.description',
                'required'  => true,
                'constraints' => [new NotBlank()],
            ]
        );

        $builder->add(
            'clientId',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.client_id.label',
                'tooltip'   => 'marello.magebridge.magento.form.client_id.description',
                'required'  => true,
                'constraints' => [new NotBlank()],
            ]
        );

        $builder->add(
            'clientSecret',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.client_secret.label',
                'tooltip'   => 'marello.magebridge.magento.form.client_secret.description',
                'required'  => true,
                'constraints' => [new NotBlank()],
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
            'tokenKey',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.token.label',
                'tooltip'   => 'marello.magebridge.magento.form.token.description',
                'required'  => true,
                'constraints' => [new NotBlank()],
                'disabled'  => true,
            ]
        );

        $builder->add(
            'tokenSecret',
            'text',
            [
                'label'     => 'marello.magebridge.magento.form.token_secret.label',
                'tooltip'   => 'marello.magebridge.magento.form.token_secret.description',
                'required'  => true,
                'constraints' => [new NotBlank()],
                'disabled'  => true,
            ]
        );

        $builder->add(
            'salesChannels',
            'marello_sales_saleschannel_multi_select',
            [
                'label' => 'marello.magebridge.magento.form.sales_channel.label',
                'required' => true,
                'disabled' => $isExisting,
            ]
        );

//        $builder->add(
//            $builder->create('websites', 'hidden')
//                ->addViewTransformer(new ArrayToJsonTransformer())
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
