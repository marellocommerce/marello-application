<?php

namespace Marello\Bundle\ShippingBundle\Form\Type;

use Marello\Bundle\ShippingBundle\Provider\ShippingMethodChoicesProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingMethodSelectType extends AbstractType
{
    const NAME = 'marello_shipping_method_select';
    
    /**
     * @var ShippingMethodChoicesProviderInterface
     */
    protected $shippingMethodChoicesProvider;

    /**
     * @param ShippingMethodChoicesProviderInterface $shippingMethodChoicesProvider
     */
    public function __construct(ShippingMethodChoicesProviderInterface $shippingMethodChoicesProvider)
    {
        $this->shippingMethodChoicesProvider = $shippingMethodChoicesProvider;
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
        return $this->shippingMethodChoicesProvider->getMethods();
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
