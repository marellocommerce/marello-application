<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PricingBundle\Provider\ChannelPriceProvider;
use Marello\Bundle\PricingBundle\Subtotal\Model\LineItemsAwareInterface;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\PricingBundle\Subtotal\Provider\AbstractSubtotalProvider;
use Marello\Bundle\PricingBundle\Subtotal\Provider\SubtotalProviderInterface;
use Marello\Bundle\OrderBundle\Model\QuantityAwareInterface;
use Oro\Bundle\CurrencyBundle\Entity\PriceAwareInterface;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Symfony\Component\Translation\TranslatorInterface;

class OrderItemsSubtotalProvider extends AbstractSubtotalProvider
{
    const TYPE = 'subtotal';
    const NAME = 'marello.order.subtotals.subtotal';

    /**
     * @var ChannelPriceProvider
     */
    protected $channelPriceProvider;


    /**
     * @param TranslatorInterface $translator
     * @param RoundingServiceInterface $rounding
     * @param DefaultCurrencyProviderInterface $defaultCurrencyProvider
     * @param ChannelPriceProvider $channelPriceProvider
     */
    public function __construct(
        TranslatorInterface $translator,
        RoundingServiceInterface $rounding,
        DefaultCurrencyProviderInterface $defaultCurrencyProvider,
        ChannelPriceProvider $channelPriceProvider
    ) {
        parent::__construct($translator, $rounding, $defaultCurrencyProvider);
        $this->channelPriceProvider = $channelPriceProvider;
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
    public function isSupported($entity)
    {
        return $entity instanceof Order;
    }

    /**
     * Get line items subtotal
     *
     * @param LineItemsAwareInterface $entity
     *
     * @return Subtotal
     */
    public function getSubtotal($entity)
    {
        $amount = $this->isSupported($entity)
            ? $this->getRecalculatedSubtotalAmount($entity)
            : 0.0;

        return $this->createSubtotal($entity, $amount);
    }

    /**
     * @param object $entity
     * @param float $amount
     * @return Subtotal
     */
    protected function createSubtotal($entity, $amount)
    {
        $subtotal = new Subtotal([]);
        $subtotal->setLabel($this->translator->trans(self::NAME . '.label'))
            ->setType(self::TYPE)
            ->setVisible($amount > 0)
            ->setAmount($amount)
            ->setCurrency($this->getBaseCurrency($entity));

        return $subtotal;
    }

    /**
     * @param LineItemsAwareInterface $entity
     * @return float
     */
    protected function getRecalculatedSubtotalAmount($entity)
    {
        $subtotalAmount = 0.0;
        foreach ($entity->getItems() as $lineItem) {
            if ($lineItem instanceof PriceAwareInterface) {
                $subtotalAmount += $this->getRowTotal($lineItem);
            }
        }
        
        if (0 !== count($this->dependOnProviders)) {
            foreach ($this->dependOnProviders as $v) {
                /** @var SubtotalProviderInterface $provider */
                $provider = $v['provider'];
                $operation = $v['operation'];
                if ($provider->isSupported($entity)) {
                    $dependAmount = (float)$provider->getSubtotal($entity)->getAmount();
                    if ($operation === Subtotal::OPERATION_ADD) {
                        $subtotalAmount += $dependAmount;
                    } elseif ($operation === Subtotal::OPERATION_SUBTRACTION) {
                        $subtotalAmount -= $dependAmount;
                    }
                    if ($subtotalAmount < 0) {
                        $subtotalAmount = 0.0;
                    }
                }
            }
        }

        return $this->rounding->round($subtotalAmount);
    }

    /**
     * @param OrderItem $orderItem
     * @return float|int
     */
    public function getRowTotal(OrderItem $orderItem)
    {
        $rowTotal = 0.0;
        $salesChannel = $orderItem->getOrder()->getSalesChannel();
        $product = $orderItem->getProduct();
        if ($product) {
            $productSalesChannels = $product->getChannels();
            if ($productSalesChannels->contains($salesChannel)) {
                $channelPrice = $this->channelPriceProvider->getChannelPrice($salesChannel, $product);

                if (isset($channelPrice['price'])) {
                    $rowTotal = (float)$channelPrice['price'];
                } else {
                    $rowTotal = (float)$this->channelPriceProvider->getDefaultPrice($salesChannel, $product);
                }

                if ($orderItem instanceof QuantityAwareInterface) {
                    $rowTotal *= $orderItem->getQuantity();
                }
            }
        }

        return $rowTotal;
    }
}
