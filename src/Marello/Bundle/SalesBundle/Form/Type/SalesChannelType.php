<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Marello\Bundle\SalesBundle\Form\EventListener\SalesChannelFormSubscriber;
use Symfony\Component\Validator\Constraints\Count;

class SalesChannelType extends AbstractType
{
    const NAME = 'marello_sales_channel';

    /** @var SalesChannelFormSubscriber $salesChannelFormSubscriber */
    protected $salesChannelFormSubscriber;

    /**
     * SalesChannelType constructor.
     * @param SalesChannelFormSubscriber $salesChannelFormSubscriber
     */
    public function __construct(SalesChannelFormSubscriber $salesChannelFormSubscriber)
    {
        $this->salesChannelFormSubscriber    = $salesChannelFormSubscriber;
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
            ->add('currency', 'oro_currency')
            ->add('default', 'checkbox', [
                'required' => false,
            ])
            ->add('active', 'checkbox', [
                'required' => false,
            ])
            ->add('defaultLanguage', 'entity', [
                'required' => true,
                'multiple' => false,
                'class' => 'OroLocaleBundle:Localization',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->orderBy('l.name', 'ASC');
                },
                'choice_label' => 'name'
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\SalesBundle\Entity\SalesChannel'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
