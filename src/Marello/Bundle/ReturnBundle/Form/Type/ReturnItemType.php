<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Marello\Bundle\ReturnBundle\Validator\Constraints\ReturnItemConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReturnItemType extends AbstractType
{
    const NAME = 'marello_return_item';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', 'number', [
            'data' => 0,
        ]);
        $builder->add('reason', 'oro_enum_choice', [
            'enum_code'   => 'marello_return_reason',
            'required'    => true,
            'label'       => 'marello.return.returnentity.reason.label',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\ReturnBundle\Entity\ReturnItem',
            'constraints'        => new ReturnItemConstraint(),
            'cascade_validation' => true,
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
