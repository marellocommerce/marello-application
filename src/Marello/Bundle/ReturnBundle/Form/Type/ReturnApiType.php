<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Marello\Bundle\OrderBundle\Form\Type\OrderItemApiType;
use Marello\Bundle\ReturnBundle\Form\DataTransformer\OrderToOrderNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ReturnApiType extends AbstractType
{
    const NAME = 'marello_return_api';

    /** @var OrderToOrderNumberTransformer */
    protected $orderToOrderNumberTransformer;

    /**
     * ReturnApiType constructor.
     *
     * @param OrderToOrderNumberTransformer $orderToOrderNumberTransformer
     */
    public function __construct(OrderToOrderNumberTransformer $orderToOrderNumberTransformer)
    {
        $this->orderToOrderNumberTransformer = $orderToOrderNumberTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order', 'text', [
                'required'    => true,
                'constraints' => new NotNull(),
            ])
            ->add('returnNumber', 'text', [
                'required' => false,
            ])
            ->add('returnItems', 'collection', [
                'type'      => OrderItemApiType::NAME,
                'allow_add' => true,
            ]);

        $builder->get('order')->addModelTransformer($this->orderToOrderNumberTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Marello\Bundle\ReturnBundle\Entity\ReturnEntity',
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }
}
