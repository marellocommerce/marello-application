<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class InventoryItemType extends AbstractType
{
    const NAME = 'marello_inventory_item';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('modifyOperator', 'choice', [
                'choices'            => [
                    InventoryItem::MODIFY_OPERATOR_INCREASE => 'increase',
                    InventoryItem::MODIFY_OPERATOR_DECREASE => 'decrease',
                ],
                'mapped'             => false,
                'translation_domain' => 'MarelloInventoryChangeDirection',
            ])
            ->add('modifyAmount', 'number', [
                'mapped'      => false,
                'constraints' => new GreaterThanOrEqual(0),
                'data'        => 0,
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var InventoryItem $data */
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data) {
                return;
            }

            $operator = $form->get('modifyOperator')->getData();
            $amount   = $form->get('modifyAmount')->getData();

            if ($operator === InventoryItem::MODIFY_OPERATOR_INCREASE) {
                /*
                 * Increase amount if operator is increase.
                 */
                $data->modifyQuantity($amount);
            } elseif (($data->getQuantity() - $amount) >= 0) {
                /*
                 * Else (operator is decrease) and resulting amount would be still positive... decrease quantity.
                 */
                $data->modifyQuantity(-$amount);
            } else {
                /*
                 * If operation would create a negative value. Add form error.
                 */
                $form->get('modifyAmount')->addError(new FormError('Resulting quantity should be greater than 0.'));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\InventoryBundle\Entity\InventoryItem',
            'cascade_validation' => true,
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
