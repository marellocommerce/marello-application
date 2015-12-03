<?php

namespace Marello\Bundle\PricingBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;

//use OroB2B\Bundle\PricingBundle\Entity\PriceList;
use Marello\Bundle\PricingBundle\Entity\Repository\ProductPriceRepository;

class ProductPriceProvider
{
    const PRICE_IDENTIFIER = 'product-id-';
    /** @var ManagerRegistry $registry  */
    protected $registry;

    /** @var string $className */
    protected $className;

    /**
     * ProductPriceProvider constructor.
     * @param ManagerRegistry $registry
     * @param $className
     */
    public function __construct(ManagerRegistry $registry, $className)
    {
        $this->registry = $registry;
        $this->className = $className;
    }

    public function getPriceBySalesChannel($channel, array $products)
    {
        $result = array();

        $prices = $this->getRepository()->findBySalesChannel($channel,$products);
        if ($prices) {
            foreach ($prices as $price) {
                $result[self::PRICE_IDENTIFIER.$price->getProduct()->getId()] = [
                    'value' => (float)$price->getValue(),
                ];
            }
        }

        return $result;
    }

    /**
     * @return ProductPriceRepository
     */
    protected function getRepository()
    {
        return $this->registry->getManagerForClass($this->className)->getRepository($this->className);
    }
}
