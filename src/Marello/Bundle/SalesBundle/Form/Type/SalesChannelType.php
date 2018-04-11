<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\PricingBundle\Form\EventListener\CurrencySubscriber;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelType extends AbstractType
{
    const NAME = 'marello_sales_channel';

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
            ->add('channelType')
            ->add('currency', CurrencyType::class)
            ->add('default', CheckboxType::class, [
                'required' => false,
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
            ])
            ->add('localization', EntityType::class, [
                'required' => true,
                'multiple' => false,
                'class' => 'OroLocaleBundle:Localization',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->orderBy('l.name', 'ASC');
                },
                'choice_label' => 'name'
            ])
            ->add('locale')
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
    public function getName()
    {
        return self::NAME;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
