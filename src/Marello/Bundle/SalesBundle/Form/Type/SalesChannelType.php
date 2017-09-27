<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Form\EventListener\SalesChannelFormSubscriber;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesChannelType extends AbstractType
{
    const NAME = 'marello_sales_channel';

    /**
     * @var SalesChannelFormSubscriber
     */
    protected $salesChannelFormSubscriber;

    /**
     * @param SalesChannelFormSubscriber $salesChannelFormSubscriber
     */
    public function __construct(SalesChannelFormSubscriber $salesChannelFormSubscriber)
    {
        $this->salesChannelFormSubscriber = $salesChannelFormSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->salesChannelFormSubscriber);
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
            ->add('locale');
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
