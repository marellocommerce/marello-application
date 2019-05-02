<?php

namespace Marello\Bundle\PricingBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PricingBundle\Entity\BasePrice;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\OrderBundle\Provider\OrderItem\AbstractOrderItemFormChangesProvider;

class ChannelPriceProvider extends AbstractOrderItemFormChangesProvider
{
    /**
     * @var ManagerRegistry $registry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        $submittedData = $context->getSubmittedData();
        $form = $context->getForm();
        $order = $form->getData();
        if ($order instanceof Order) {
            $salesChannel = $order->getSalesChannel();
        } else {
            return;
        }
        $productIds = [];
        foreach ($submittedData[self::ITEMS_FIELD] as $item) {
            $productIds[] = (int)$item['product'];
        }
        
        $data = [];
        $products = $this->getProductRepository()->findBySalesChannel($salesChannel->getId(), $productIds);

        foreach ($products as $product) {
            $priceValue = $this->getDefaultPrice($salesChannel, $product);
            $channelPrice = $this->getChannelPrice($salesChannel, $product);

            if ($channelPrice['hasPrice']) {
                $priceValue = $channelPrice['price'];
            }

            $data[$this->getIdentifier($product->getId())]['value'] = $priceValue;
        }
        foreach ($order->getItems() as &$item) {
            if ($product = $item->getProduct()) {
                $productId = $product->getId();
                if (isset($data[$this->getIdentifier($productId)])) {
                    $item->setPrice($data[$this->getIdentifier($productId)]['value']);
                }
            }
        }
        $result = $context->getResult();
        $result[self::ITEMS_FIELD]['price'] = $data;
        $context->setResult($result);
    }

    /**
     * Get channel price
     * @param SalesChannel $channel
     * @param Product $product
     * @return array $data
     */
    public function getChannelPrice($channel, $product)
    {
        $data = ['hasPrice' => false];
        $assembledChannelPriceList = $this->getAssembledChannelPriceListRepository()->findOneBy(
            [
                'channel' => $channel->getId(),
                'product' => $product->getId(),
                'currency' => $channel->getCurrency()
            ]
        );

        if ($assembledChannelPriceList) {
            /** @var ProductChannelPrice $price */
            $price = $assembledChannelPriceList->getSpecialPrice() ?: $assembledChannelPriceList->getDefaultPrice();

            if ($price instanceof BasePrice) {
                $data['hasPrice'] = true;
                $data['price'] = (float)$price->getValue();
            }
        }

        return $data;
    }

    /**
     * Get Default price by currency for product
     * @param SalesChannel $channel
     * @param Product $product
     * @return float
     */
    public function getDefaultPrice($channel, $product)
    {
        $currency = $channel->getCurrency();
        /** @var AssembledPriceList $assembledPriceList */
        $assembledPriceList = $this->getAssembledPriceListRepository()->findOneBy(
            ['product' => $product->getId(), 'currency' => $currency]
        );

        if (!$assembledPriceList) {
            return null;
        }

        $price = $assembledPriceList->getSpecialPrice() ? : $assembledPriceList->getDefaultPrice();

        return $price instanceof BasePrice ? (float)$price->getValue() : null;
    }

    /**
     * @return ObjectRepository|ProductRepository
     */
    protected function getProductRepository()
    {
        return $this->getRepository(Product::class);
    }

    /**
     * @return ObjectRepository
     */
    protected function getAssembledPriceListRepository()
    {
        return $this->getRepository(AssembledPriceList::class);
    }

    /**
     * @return ObjectRepository
     */
    protected function getAssembledChannelPriceListRepository()
    {
        return $this->getRepository(AssembledChannelPriceList::class);
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    protected function getRepository($className)
    {
        return $this->registry->getManagerForClass($className)->getRepository($className);
    }

    /**
     * @param int $id
     * @return string
     */
    protected function getIdentifier($id)
    {
        return sprintf('%s%s', self::IDENTIFIER_PREFIX, $id);
    }
}
