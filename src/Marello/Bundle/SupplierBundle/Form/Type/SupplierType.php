<?php

namespace Marello\Bundle\SupplierBundle\Form\Type;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\CurrencyBundle\Form\Type\CurrencyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class SupplierType extends AbstractType
{
    const NAME = 'marello_supplier_form';

    public static $nonStreetAttributes = [
        'namePrefix',
        'firstName',
        'middleName',
        'lastName',
        'nameSuffix'
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                ['constraints' => new NotNull()]
            )
            ->add(
                'address',
                'marello_address',
                ['required' => true]
            )
            ->add(
                'priority',
                'text',
                ['constraints' => new NotNull()]
            )
            ->add('canDropship')
            ->add('isActive')
            ->add('email')
            ->add('currency', CurrencyType::class)
            ->add(
                'poSendBy',
                ChoiceType::class,
                [
                    'label' => 'marello.supplier.po_send_by.label',
                    'mapped' => true,
                    'choices' => [
                        Supplier::SEND_PO_MANUALLY => 'marello.supplier.po_send_by.manual',
                        Supplier::SEND_PO_BY_EMAIL => 'marello.supplier.po_send_by.email'
                    ]
                ]
            );

        $this->removeNonStreetFieldsFromAddress($builder, 'address');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Marello\Bundle\SupplierBundle\Entity\Supplier',
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

    /**
     * Remove all non street attributes from address in supplier
     * @param FormBuilderInterface $builder
     * @param $childName
     */
    protected function removeNonStreetFieldsFromAddress(FormBuilderInterface $builder, $childName)
    {
        $address = $builder->get($childName);
        foreach (self::$nonStreetAttributes as $attribute) {
            $address->remove($attribute);
        }
    }
}
