<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\ReturnBundle\Form\EventListener\ReturnItemTypeSubscriber;
use Marello\Bundle\ReturnBundle\Validator\Constraints\ReturnItemConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ReturnItemType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_return_item';

    /**
     * @var ReturnItemTypeSubscriber
     */
    protected $returnItemTypeSubscriber;

    /**
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
            $builder->add('quantity', NumberType::class);
        } else {
            $builder->add('quantity', NumberType::class, [
                'data' => 0,
            ]);
        }

        $builder->addEventSubscriber($this->returnItemTypeSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => ReturnItem::class,
            'update'             => false,
            'constraints'        => function (Options $options) {
                return [
                    new ReturnItemConstraint(!$options['update']),
                    new Valid()
                ];
            },
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
