<?php

namespace Marello\Bundle\WebhookBundle\Form\Type;

use Marello\Bundle\WebhookBundle\Model\WebhookEventInterface;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventSelectType extends AbstractType
{
    public const BLOCK_PREFIX = 'marello_webhook_event_select';


    /**
     * @var EnumValueProvider
     */
    protected $enumValueProvider;

    /**
     * @param EnumValueProvider $enumValueProvider
     */
    public function __construct(EnumValueProvider $enumValueProvider)
    {
        $this->enumValueProvider = $enumValueProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices'  => $this->enumValueProvider
                    ->getEnumChoicesByCode(WebhookEventInterface::WEBHOOK_EVENT_ENUM_CLASS),
                'multiple' => false,
                'configs'  => [
                    'width'      => '400px',
                    'allowClear' => true,
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return Select2ChoiceType::class;
    }
}
