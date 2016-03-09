<?php

namespace Marello\Bundle\PricingBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Marello\Bundle\PricingBundle\Entity\Repository\ProductPriceRepository;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class ProductChannelPriceProvider
{
    const PRICE_IDENTIFIER = 'product-id-';
    const CURRENCY_IDENTIFIER = 'currency-';

    /** @var ManagerRegistry $registry  */
    protected $registry;

    /** @var string $productPriceClassName */
    protected $productPriceClassName;

    /** @var string $productClassName */
    protected $productClassName;

    /** @var $translator */
    protected $translator;

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * ProductPriceProvider constructor.
     * @param ManagerRegistry $registry
     * @param $translator
     * @param LocaleSettings $localeSettings
     */
    public function __construct(
        ManagerRegistry $registry,
        $translator,
        LocaleSettings $localeSettings
    ) {
        $this->registry = $registry;
        $this->translator = $translator;
        $this->localeSettings = $localeSettings;
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
            $price = $this->getRepository(ProductChannelPrice::class)->findOneBySalesChannel($channel, $product);
            $pricesCount = count($price);
            $priceValue = null;
            if ($pricesCount > 0 && $pricesCount < 2) {
                $price = array_shift($price);
                $priceValue = (float)$price['price_value'];
            } else {
                $priceValue = (float)$product->getPrice();
            }
            $result[self::PRICE_IDENTIFIER.$product->getId()] = [
                'value' => $priceValue,
            ];
        }

        return $result;
    }

    /**
     * Get currency for channel.
     * @param $channelId
     * @return array
     */
    public function getCurrency($channelId)
    {
        $channel = $this->getRepository(SalesChannel::class)->find($channelId);
        $result[self::CURRENCY_IDENTIFIER.$channel->getId()] = [
            'currencyCode' => $channel->getCurrency(),
            'currencySymbol' => $this->localeSettings->getCurrencySymbolByCurrency($channel->getCurrency())
        ];

        return $result;
    }

    /**
     * @param string $className
     * @return ProductPriceRepository | ProductRepository
     */
    protected function getRepository($className)
    {
        return $this->registry->getManagerForClass($className)->getRepository($className);
    }
}
