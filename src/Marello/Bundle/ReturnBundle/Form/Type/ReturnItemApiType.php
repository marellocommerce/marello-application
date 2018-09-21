<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ReturnItemApiType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_return_item_api';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', NumberType::class)
            ->add('orderItem', EntityType::class, [
                'class' => 'MarelloOrderBundle:OrderItem',
            ]);
        $builder->add('reason', EnumChoiceType::class, [
            'enum_code'   => 'marello_return_reason',
            'required'    => true,
            'constraints' => new NotNull()
        ]);

        $builder->add('status', EnumChoiceType::class, [
            'enum_code' => 'marello_return_status',
            'required'  => true,
            'label'     => 'marello.return.returnitem.status.label',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => ReturnItem::class
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
