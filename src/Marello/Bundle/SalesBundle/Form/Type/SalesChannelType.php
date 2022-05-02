<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Marello\Bundle\PricingBundle\Form\EventListener\CurrencySubscriber;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencyType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizationSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_sales_channel';

    /**
     * @var CurrencySubscriber
     */
    protected $currencySubscriber;

    /**
     * @param CurrencySubscriber $currencySubscriber
     */
    public function __construct(CurrencySubscriber $currencySubscriber)
    {
        $this->currencySubscriber = $currencySubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->currencySubscriber);
        $builder
            ->add('name')
            ->add('code')
            ->add('channelType', SalesChannelTypeSelectType::class, [
                'required' => true
            ])
            ->add('currency', CurrencyType::class)
            ->add('default', CheckboxType::class, [
                'required' => false
            ])
            ->add('active', CheckboxType::class, [
                'required' => false
            ])
            ->add('localization', LocalizationSelectType::class, [
                'required' => false
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetDataListener']);
    }

    /**
     * @param FormEvent $event
     */
    public function preSetDataListener(FormEvent $event)
    {
        /** @var SalesChannel $channel */
        $channel = $event->getData();
        $form = $event->getForm();

        if ($channel->getGroup() === null || $channel->getGroup()->isSystem() === true) {
            $form->add('createOwnGroup', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'marello.sales.saleschannel.create_own_group.label'
            ]);
        }

        if ($channel->getCode() !== null) {
            // disable code field for sc's
            FormUtils::replaceField($form, 'code', ['disabled' => true]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesChannel::class
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
