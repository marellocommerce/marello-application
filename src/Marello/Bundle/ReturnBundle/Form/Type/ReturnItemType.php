<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Marello\Bundle\ReturnBundle\Validator\Constraints\ReturnItemConstraint;
use Marello\Bundle\ReturnBundle\Form\EventListener\ReturnItemTypeSubscriber;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReturnItemType extends AbstractType
{
    const NAME = 'marello_return_item';

    /** @var ReturnItemTypeSubscriber $returnItemTypeSubscriber */
    protected $returnItemTypeSubscriber;

    /**
     * ReturnType constructor.
     *
     * @param ReturnItemTypeSubscriber $returnItemTypeSubscriber
     */
    public function __construct(ReturnItemTypeSubscriber $returnItemTypeSubscriber)
    {
        $this->returnItemTypeSubscriber = $returnItemTypeSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['update']) {
            $builder->add('quantity', 'number');
        } else {
            $builder->add('quantity', 'number', [
                'data' => 0,
            ]);
        }
        $builder->add('reason', 'oro_enum_choice', [
            'enum_code' => 'marello_return_reason',
            'required'  => true,
            'label'     => 'marello.return.returnitem.reason.label',
        ]);

        $builder->addEventSubscriber($this->returnItemTypeSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\ReturnBundle\Entity\ReturnItem',
            'update'             => false,
            'constraints'        => function (Options $options) {
                return new ReturnItemConstraint(!$options['update']);
            },
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
