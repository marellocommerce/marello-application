<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Model\ReplenishmentOrderStepOne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReplenishmentOrderStepOneType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_replenishment_order_step_one';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'marelloenterprise.replenishment.replenishmentorder.type.label',
                    'choices' => [
                        'marelloenterprise.replenishment.replenishmentorder.type.automated.label' => ReplenishmentOrderStepOne::AUTOMATED_TYPE,
                        'marelloenterprise.replenishment.replenishmentorder.type.manual.label' => ReplenishmentOrderStepOne::MANUAL_TYPE,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReplenishmentOrderStepOne::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
