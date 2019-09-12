<?php

namespace Marello\Bundle\SubscriptionBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\OrderBundle\Converter\ShippingPricesConverter;
use Marello\Bundle\ShippingBundle\Context\ShippingContextFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Marello\Bundle\ShippingBundle\Provider\Price\ShippingPriceProviderInterface;
use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Marello\Bundle\SubscriptionBundle\Mapper\SubscriptionToOrderMapper;

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
     * @var SubscriptionToOrderMapper
     */
    protected $subscriptionToOrderMapper;

    /**
     * @param ShippingContextFactoryInterface $factory
     * @param ShippingPricesConverter $priceConverter
     * @param ShippingPriceProviderInterface|null $priceProvider
     * @param ShippingServiceRegistry $registry
     * @param SubscriptionToOrderMapper $subscriptionToOrderMapper
     */
    public function __construct(
        ShippingContextFactoryInterface $factory,
        ShippingPricesConverter $priceConverter,
        ShippingPriceProviderInterface $priceProvider = null,
        ShippingServiceRegistry $registry,
        SubscriptionToOrderMapper $subscriptionToOrderMapper
    ) {
        $this->factory = $factory;
        $this->priceConverter = $priceConverter;
        $this->priceProvider = $priceProvider;
        $this->registry = $registry;
        $this->subscriptionToOrderMapper = $subscriptionToOrderMapper;
    }
    
    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $form = $context->getForm();
        $subscription = $form->getData();
        $result = $context->getResult();
        $result[self::POSSIBLE_SHIPPING_METHODS_KEY] = $this->getPossibleShippingMethods($subscription);
        $context->setResult($result);
    }

    /**
     * @param Subscription $subscription
     * @return array
     */
    private function getPossibleShippingMethods(Subscription $subscription)
    {
        $data = [];
        if ($this->priceProvider) {
            $shippingContextArray = $this->factory->create($this->subscriptionToOrderMapper->map($subscription));
            $shippingContext = !empty($shippingContextArray) ? reset($shippingContextArray) : null;
            if (!$shippingContext) {
                return $data;
            }
            $shippingMethodViews = $this->priceProvider
                ->getApplicableMethodsViews($shippingContext)
                ->toArray();
            $data = $this->priceConverter->convertPricesToArray($shippingMethodViews);
        }
        
        return $data;
    }
}
