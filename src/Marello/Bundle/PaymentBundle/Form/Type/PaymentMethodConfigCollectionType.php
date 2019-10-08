<?php

namespace Marello\Bundle\PaymentBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Marello\Bundle\PaymentBundle\Form\EventSubscriber\MethodConfigCollectionSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodConfigCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_payment_method_config_collection';

    /**
     * @var MethodConfigCollectionSubscriber
     */
    protected $subscriber;

    /**
     * @param MethodConfigCollectionSubscriber $subscriber
     */
    public function __construct(MethodConfigCollectionSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'show_form_when_empty' => false,
            'allow_add' => true,
            'entry_type' => PaymentMethodConfigType::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allow_add'] = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
