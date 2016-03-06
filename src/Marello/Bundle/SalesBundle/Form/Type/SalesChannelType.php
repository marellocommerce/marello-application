<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class SalesChannelType extends AbstractType
{
    const NAME = 'marello_sales_channel';

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('code')
            ->add('channelType')
            ->add('currency', 'oro_currency',[
                'data' => $this->localeSettings->getCurrency()
            ])
            ->add('default', 'checkbox',[
                'required' => false
            ])
            ->add('active', 'checkbox',[
                'required' => false
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\SalesBundle\Entity\SalesChannel',
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
