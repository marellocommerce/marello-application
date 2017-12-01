<?php

namespace Marello\Bundle\ShippingBundle\Form\Type;

use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingMethodSelectType extends AbstractType
{
    const NAME = 'marello_shipping_method_select';
    
    /**
     * @var ShippingServiceRegistry
     */
    protected $shippingServiceRegistry;

    /**
     * @param ShippingServiceRegistry $shippingServiceRegistry
     */
    public function __construct(ShippingServiceRegistry $shippingServiceRegistry)
    {
        $this->shippingServiceRegistry = $shippingServiceRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'expanded' => true,
                'choices' => $this->getChoices(),
            ]);
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        $choices = [];
        foreach (array_keys($this->shippingServiceRegistry->getIntegrations()) as $method) {
            $choices[$method] = strtoupper($method);
        }

        return $choices;
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
    public function getParent()
    {
        return ChoiceType::class;
    }
}
