<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Marello\Bundle\ReturnBundle\Validator\Constraints\ReturnItemConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ReturnItemApiType extends AbstractType
{
    const NAME = 'marello_return_item_api';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', 'number')
            ->add('orderItem', 'entity', [
                'class' => 'MarelloOrderBundle:OrderItem',
            ]);
        $builder->add('reason', 'oro_enum_choice', [
            'enum_code'   => 'marello_return_reason',
            'required'    => true,
            'constraints' => new NotNull()
        ]);

        $builder->add('status', 'oro_enum_choice', [
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
            'data_class'  => 'Marello\Bundle\ReturnBundle\Entity\ReturnItem'
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
