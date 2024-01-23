<?php

namespace Marello\Bundle\WebhookBundle\Form\Type;

use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Oro\Bundle\FormBundle\Form\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Valid;

class WebhookType extends AbstractType
{
    public const BLOCK_PREFIX = 'marello_webhook_webhook';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'marello.webhook.name.label',
                    'tooltip' => 'marello.webhook.name.label.description',
                    'required' => true
                ]
            )->add(
                'callbackUrl',
                TextType::class,
                [
                    'constraints' => [new Url()],
                    'label' => 'marello.webhook.callback_url.label',
                    'required' => true,
                    'tooltip' => 'marello.webhook.callback_url.description',
                ]
            )->add(
                'secret',
                TextType::class,
                [
                    'label' => 'marello.webhook.secret.label',
                    'required' => true,
                    'tooltip' => 'marello.webhook.secret.description',
                ]
            )->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'marello.webhook.enabled.label',
                ]
            )->add(
                'event',
                EventSelectType::class,
                [
                    'label' => 'marello.webhook.event.label',
                    'required' => true
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => Webhook::class,
            'constraints' => [new Valid()]
        ]);
    }

    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
