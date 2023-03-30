<?php

namespace Marello\Bundle\WebhookBundle\Form\Type;

use Marello\Bundle\WebhookBundle\Entity\WebhookNotificationSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Used on Integration Create/Edit page to show fields for Slack Channel
 */
class WebhookSettingsType extends AbstractType
{
    public const BLOCK_PREFIX = 'marello_webhook_notification_settings';

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'webhookSignatureAlgo',
                TextType::class,
                [
                    'label' => 'marello.webhook.signature_algo.form.label',
                    'required' => false,
                    'tooltip' => 'marello.webhook.signature_algo.form.description',
                    'empty_data' => 'John Doe',
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WebhookNotificationSettings::class,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
