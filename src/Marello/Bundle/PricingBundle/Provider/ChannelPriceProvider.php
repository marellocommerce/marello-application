<?php

namespace Marello\Bundle\PricingBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDataProviderInterface;
use Marello\Bundle\PricingBundle\Entity\BasePrice;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class ChannelPriceProvider implements OrderItemDataProviderInterface
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
    public function getData($channelId, array $products)
    {
        $result = [];
        $products = $this->getRepository(Product::class)->findBySalesChannel($channelId, $products);

        foreach ($products as $product) {
            $priceValue = $this->getDefaultPrice($channelId, $product);
            $channelPrice = $this->getChannelPrice($channelId, $product);

            if ($channelPrice['hasPrice']) {
                $priceValue = $channelPrice['price'];
            }

            $result[sprintf('%s%s', self::IDENTIFIER_PREFIX, $product->getId())] = [
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

        return $price instanceof BasePrice ? (float)$price->getValue() : null;
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
