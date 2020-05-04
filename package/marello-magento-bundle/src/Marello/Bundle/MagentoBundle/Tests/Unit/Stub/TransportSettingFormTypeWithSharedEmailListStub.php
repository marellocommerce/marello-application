<?php

namespace Marello\Bundle\MagentoBundle\Tests\Unit\Stub;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Marello\Bundle\MagentoBundle\Form\Type\AbstractTransportSettingFormType;
use Marello\Bundle\MagentoBundle\Form\Type\SharedGuestEmailListType;

class TransportSettingFormTypeWithSharedEmailListStub extends AbstractType
{
    const NAME = 'oro_magento_soap_transport_setting_form_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME,
            SharedGuestEmailListType::NAME
        );
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
