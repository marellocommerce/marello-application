<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\OrderBundle\Converter\ShippingPricesConverter;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Context\ShippingContextFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Marello\Bundle\ShippingBundle\Provider\Price\ShippingPriceProviderInterface;

class PossibleShippingMethodsProvider implements FormChangesProviderInterface
{
    const POSSIBLE_SHIPPING_METHODS_KEY = 'possibleShippingMethods';
    
    /**
     * @var ShippingContextFactoryInterface
     */
    protected $factory;

    /**
     * @var ShippingPriceProviderInterface|null
     */
    protected $priceProvider;

    /**
     * @var ShippingPricesConverter
     */
    protected $priceConverter;

    /**
     * @var ShippingServiceRegistry
     */
    protected $registry;

    /**
     * @param ShippingContextFactoryInterface $factory
     * @param ShippingPricesConverter $priceConverter
     * @param ShippingPriceProviderInterface|null $priceProvider
     */
    public function __construct(
        ShippingContextFactoryInterface $factory,
        ShippingPricesConverter $priceConverter,
        ShippingPriceProviderInterface $priceProvider = null,
        ShippingServiceRegistry $registry
    ) {
        $this->factory = $factory;
        $this->priceConverter = $priceConverter;
        $this->priceProvider = $priceProvider;
        $this->registry = $registry;
    }
    
    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        $order = $form->getData();
        $result = $context->getResult();
        $result[self::POSSIBLE_SHIPPING_METHODS_KEY] = $this->getPossibleShippingMethods($order);
        $context->setResult($result);
    }

    /**
     * @param Order $order
     * @return array
     */
    private function getPossibleShippingMethods(Order $order)
    {
        $data = [];
        if ($this->priceProvider) {
            $shippingContext = $this->factory->create($order);
            $shippingMethodViews = $this->priceProvider
                ->getApplicableMethodsViews($shippingContext)
                ->toArray();
            $data = $this->priceConverter->convertPricesToArray($shippingMethodViews);
        }
        
        return $data;
    }
}
