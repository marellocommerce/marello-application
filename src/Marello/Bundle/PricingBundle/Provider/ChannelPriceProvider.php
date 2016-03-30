<?php

namespace Marello\Bundle\PricingBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class ChannelPriceProvider
{
    const PRICE_IDENTIFIER = 'product-id-';

    /** @var ManagerRegistry $registry  */
    protected $registry;

    /** @var $translator */
    protected $translator;

    /**
     * ChannelPriceProvider constructor.
     * @param ManagerRegistry $registry
     * @param $translator
     */
    public function __construct(
        ManagerRegistry $registry,
        $translator
    ) {
        $this->registry = $registry;
        $this->translator = $translator;
    }

    /**
     * Get prices for each channel or get default price.
     * if no price is available, it is not sold in the selected channel.
     * @param $channel
     * @param array $products
     * @return array
     */
    public function getPrices($channel, array $products)
    {
        $result = $this->getPricesBySalesChannel($channel, $products);
        $productCount = count($products);
        $resultCount = count($result);

        if ($productCount !== $resultCount) {
            foreach ($products as $product) {
                if (!array_key_exists(self::PRICE_IDENTIFIER . $product['product'], $result)) {
                    $result[self::PRICE_IDENTIFIER . $product['product']] = [
                        'message' => $this->translator
                            ->trans('marello.pricing.productprice.messages.product_not_salable'),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * get prices for each channel or get default price
     * @param $channel
     * @param $products
     * @return array
     */
    protected function getPricesBySalesChannel($channel, $products)
    {
        $result = [];
        $products = $this->getRepository(Product::class)->findBySalesChannel($channel, $products);

        foreach ($products as $product) {
            $priceValue = $this->getDefaultPrice($channel, $product);
            $channelPrice = $this->getChannelPrice($channel, $product);

            if ($channelPrice['hasPrice']) {
                $priceValue = $channelPrice['price'];
            }

            $result[self::PRICE_IDENTIFIER.$product->getId()] = [
                'value' => $priceValue,
            ];
        }

        return $result;
    }

    /**
     * Get channel price
     * @param $channel
     * @param $product
     * @return array $data
     */
    public function getChannelPrice($channel, $product)
    {
        $data = ['hasPrice' => false];
        $price = $this->getRepository(ProductChannelPrice::class)->findOneBySalesChannel($channel, $product->getId());
        $pricesCount = count($price);

        if ($pricesCount > 0 && $pricesCount < 2) {
            $price = array_shift($price);
            $data['hasPrice'] = true;
            $data['price'] = (float)$price['price_value'];
        }

        return $data;
    }

    /**
     * Get Default price by currency for product
     * @param $channel
     * @param $product
     * @return float
     */
    public function getDefaultPrice($channel, $product)
    {
        $currency = $this->getRepository(SalesChannel::class)->find($channel)->getCurrency();
        $price = $this->getRepository(ProductPrice::class)->findOneBy(
            ['product' => $product->getId(), 'currency' => $currency]
        );

        return (is_object($price)) ? (float)$price->getValue() : null;
    }

    /**
     * @param string $className
     * @return EntityRepository
     */
    protected function getRepository($className)
    {
        return $this->registry->getManagerForClass($className)->getRepository($className);
    }
}
