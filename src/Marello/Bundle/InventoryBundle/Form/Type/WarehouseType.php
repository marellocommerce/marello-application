<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Valid;

class WarehouseType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_warehouse';

    public static $nonStreetAttributes = [
        'namePrefix',
        'firstName',
        'middleName',
        'lastName',
        'nameSuffix',
        'email'
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
                [
                    'label' => 'marello.inventory.warehouse.label.label',
                    'required' => true
                ]
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
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => false,
                    'constraints' => [
                        new Email(),
                    ],
                ]
            )
            ->add(
                'notifier',
                NotifierChoiceType::class,
                [
                    'required' => true
                ]
            )
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [$this, 'preSetDataListener']
            );

        $this->removeNonStreetFieldsFromAddress($builder, 'address');
    }

    /**
     * @param FormEvent $event
     */
    public function preSetDataListener(FormEvent $event)
    {
        /** @var Warehouse $warehouse */
        $warehouse = $event->getData();
        $form = $event->getForm();

        if ($warehouse->getGroup() === null || $warehouse->getGroup()->isSystem() === true) {
            $form->add('createOwnGroup', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'marello.inventory.warehouse.form.create_own_group'
            ]);
        }

        if ($warehouse->getCode() !== null) {
            // disable code field for wh's
            FormUtils::replaceField($form, 'code', ['disabled' => true]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => Warehouse::class,
            'constraints' => [new Valid()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
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
