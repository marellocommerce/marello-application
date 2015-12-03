<?php

namespace Marello\Bundle\PricingBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\TranslationBundle\Translation\Translator;

use Marello\Bundle\PricingBundle\Entity\Repository\ProductPriceRepository;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class ProductPriceProvider
{
    const PRICE_IDENTIFIER = 'product-id-';
    /** @var ManagerRegistry $registry  */
    protected $registry;

    /** @var string $productPriceClassName */
    protected $productPriceClassName;

    /** @var string $productClassName */
    protected $productClassName;

    /** @var $translator */
    protected $translator;
    /**
     * ProductPriceProvider constructor.
     * @param ManagerRegistry $registry
     * @param $productPriceClassName
     * @param $productClassName
     * @param $translator
     */
    public function __construct(ManagerRegistry $registry,
        $productPriceClassName,
        $productClassName,
        $translator)
    {
        $this->registry = $registry;
        $this->productPriceClassName = $productPriceClassName;
        $this->productClassName = $productClassName;
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

        if($productCount !== $resultCount) {
            foreach($products as $product) {
                if(!array_key_exists(self::PRICE_IDENTIFIER.$product['product'], $result)) {
                    $result[self::PRICE_IDENTIFIER.$product['product']] = [
                        'message' => $this->translator->trans('marello.productprice.messages.no_price_found'),
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
    protected function getPricesBySalesChannel($channel,$products)
    {
        $result = array();
        $products = $this->getRepository($this->productClassName)->findBySalesChannel($channel,$products);

        foreach($products as $product) {
            $price = $this->getRepository($this->productPriceClassName)->findOneBySalesChannel($channel,$product);
            $pricesCount = count($price);
            $priceValue = null;
            if($pricesCount > 0 && $pricesCount < 2) {
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
     * @param string $className
     * @return ProductPriceRepository | ProductRepository
     */
    protected function getRepository($className)
    {
        return $this->registry->getManagerForClass($className)->getRepository($className);
    }
}
