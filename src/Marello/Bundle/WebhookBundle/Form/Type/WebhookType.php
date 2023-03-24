<?php

namespace Marello\Bundle\WebhookBundle\Form\Type;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class WebhookType extends AbstractType
{
    public const BLOCK_PREFIX = 'marello_webhook_webhook';

    public const ENABLE_STATUS = '1';
    public const DISABLE_STATUS = '0';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'marello.webhook.name.label',
                'tooltip' => 'marello.webhook.name.label.description',
                'required' => true
            ]
        );
        $builder->add(
            'callback_url',
            UrlType::class,
            [
                'label' => 'marello.webhook.callback_url.label',
                'required' => true,
                'tooltip' => 'marello.webhook.callback_url.description',
            ]
        );
        $builder->add(
            'secret',
            TextType::class,
            [
                'label' => 'marello.webhook.secret.label',
                'required' => true,
                'tooltip' => 'marello.webhook.secret.description',
            ]
        );

        $builder->add(
            'enabled',
            ChoiceType::class,
            [
            'choices' => [
                'disable' => self::DISABLE_STATUS,
                'enable' => self::ENABLE_STATUS,
            ],
            'translation_domain' => 'MarelloInventoryChangeDirection',
            'mapped' => false
        ]
        );

        $builder->add(
            'event',
            EventSelectType::class,
            [
                'label' => 'marello.webhook.event.label',
                'required' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => Webhook::class,
            'constraints' => [new Valid()]
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
