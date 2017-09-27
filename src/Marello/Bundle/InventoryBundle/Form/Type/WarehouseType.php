<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseType extends AbstractType
{
    const NAME = 'marello_warehouse';

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
                'label',
                TextType::class,
                ['required' => true]
            )
            ->add(
                'code',
                TextType::class,
                ['required' => true]
            )
            ->add(
                'address',
                AddressType::class,
                ['required' => true]
            );

        $this->removeNonStreetFieldsFromAddress($builder, 'address');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Warehouse::class,
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
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * Remove all non street attributes from address in warehouse
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
