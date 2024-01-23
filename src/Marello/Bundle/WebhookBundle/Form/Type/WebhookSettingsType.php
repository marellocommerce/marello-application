<?php

namespace Marello\Bundle\WebhookBundle\Form\Type;

use Marello\Bundle\WebhookBundle\Entity\WebhookNotificationSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookSettingsType extends AbstractType
{
    public const BLOCK_PREFIX = 'marello_webhook_notification_settings';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WebhookNotificationSettings::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
