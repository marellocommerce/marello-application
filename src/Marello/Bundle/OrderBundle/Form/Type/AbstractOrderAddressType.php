<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\AddressBundle\Form\Type\AddressType as MarelloAddressType;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\CustomerBundle\Provider\CustomerAddressProvider;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\ImportExportBundle\Serializer\Serializer;
use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;

abstract class AbstractOrderAddressType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_address';

    /**
     * @var AddressFormatter
     */
    protected $addressFormatter;

    /**
     * @var CustomerAddressProvider
     */
    protected $customerAddressProvider;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param AddressFormatter $addressFormatter
     * @param CustomerAddressProvider $customerAddressProvider
     * @param Serializer $serializer
     */
    public function __construct(
        AddressFormatter $addressFormatter,
        CustomerAddressProvider $customerAddressProvider,
        Serializer $serializer
    ) {
        $this->addressFormatter = $addressFormatter;
        $this->customerAddressProvider = $customerAddressProvider;
        $this->serializer = $serializer;
    }

    /**
     * @param Order $entity
     * @return array
     */
    abstract protected function getAddresses(Order $entity);

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $order = $options['object'];
        $isEditEnabled = $options['isEditEnabled'];

        $this->initCustomerAddressField($builder, $order, $isEditEnabled);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                unset($data['customerAddress']);
                $event->setData($data);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children as $child) {
            $child->vars['disabled'] = $options['disabled'];
            $child->vars['required'] = false;
            unset(
                $child->vars['attr']['data-validation'],
                $child->vars['attr']['data-required'],
                $child->vars['label_attr']['data-required']
            );
        }

        if ($view->offsetExists('customerAddress')) {
            $view->offsetGet('customerAddress')->vars['disabled'] = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['object','addressType'])
            ->setDefaults([
                'data_class' => MarelloAddress::class,
                'isEditEnabled' => true,
                'allow_extra_fields' => true,
            ])
            ->setAllowedValues('addressType', [AddressType::TYPE_BILLING, AddressType::TYPE_SHIPPING]);
    }

    /**
     * @param array $addresses
     * @return array
     */
    protected function getChoices(array $addresses = [])
    {
        $choices = [];
        $choices['marello.order.form.address.manual'] = 0;
        array_walk_recursive(
            $addresses,
            function ($item, $key) use (&$choices) {
                if ($item instanceof MarelloAddress) {
                    $choices[$this->addressFormatter->format($item, null, ', ')] = $key;
                }
            }
        );

        return $choices;
    }

    /**
     * @param MarelloAddress[] $addresses
     * @return array
     */
    protected function getPlainData(array $addresses = [])
    {
        $data = [];

        array_walk_recursive(
            $addresses,
            function ($item, $key) use (&$data) {
                if ($item instanceof MarelloAddress) {
                    $data[$key] = $this->serializer->normalize($item);
                }
            }
        );

        return $data;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Order $entity
     * @param bool $isEditEnabled
     */
    protected function initCustomerAddressField(
        FormBuilderInterface $builder,
        Order $entity,
        $isEditEnabled
    ): void {
        if ($isEditEnabled) {
            $addresses = $this->getAddresses($entity);

            $customerAddressOptions = [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'priority' => 50,
                'choices' => $this->getChoices($addresses),
                'configs' => ['placeholder' => 'marello.order.form.address.choose'],
                'attr' => [
                    'data-addresses' => json_encode($this->getPlainData($addresses)),
                ],
            ];

            $customerAddressOptions['configs']['placeholder'] = 'marello.order.form.address.choose_or_create';

            $builder->add('customerAddress', Select2ChoiceType::class, $customerAddressOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return MarelloAddressType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return static::BLOCK_PREFIX;
    }
}
