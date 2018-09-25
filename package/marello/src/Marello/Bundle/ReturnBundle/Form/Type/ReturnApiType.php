<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Form\DataTransformer\OrderToOrderNumberTransformer;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelSelectApiType;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ReturnApiType extends AbstractType
{
    const NAME = 'marello_return_api';

    /**
     * @var OrderToOrderNumberTransformer
     */
    protected $orderToOrderNumberTransformer;

    /**
     * @var EntityToIdTransformer
     */
    protected $salesChannelTransformer;

    /**
     * @param OrderToOrderNumberTransformer $orderToOrderNumberTransformer
     * @param EntityToIdTransformer         $salesChannelTransformer
     */
    public function __construct(
        OrderToOrderNumberTransformer $orderToOrderNumberTransformer,
        EntityToIdTransformer $salesChannelTransformer
    ) {
        $this->orderToOrderNumberTransformer    = $orderToOrderNumberTransformer;
        $this->salesChannelTransformer          = $salesChannelTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order', TextType::class, [
                'required'    => true,
                'constraints' => new NotNull(),
            ])
            ->add('returnNumber', TextType::class, [
                'required' => false,
            ])
            ->add('returnReference', TextType::class, [
                'required' => false,
            ])
            ->add('salesChannel', SalesChannelSelectApiType::class, [
                'required'    => true,
                'constraints' => new NotNull(),
            ])
            ->add('returnItems', CollectionType::class, [
                'type'         => ReturnItemApiType::NAME,
                'allow_add'    => true,
                'by_reference' => false,
            ]);

        $builder->get('order')->addModelTransformer($this->orderToOrderNumberTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'      => ReturnEntity::class,
            'csrf_protection' => false,
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
