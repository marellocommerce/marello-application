<?php

namespace Marello\Bundle\ProductBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDataProviderInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class ProductTaxCodeProvider implements OrderItemDataProviderInterface
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
        /** @var Product[] $products */
        $products = $this->getRepository(Product::class)->findBySalesChannel($channelId, $products);
        $channel = $this->getRepository(SalesChannel::class)->find($channelId);
        foreach ($products as $product) {
            $taxCode = $product->getSalesChannelTaxCode($channel) ? : $product->getTaxCode();
            $result[sprintf('%s%s', self::IDENTIFIER_PREFIX, $product->getId())] = [
                'id' => $taxCode->getId(),
                'code' => $taxCode->getCode(),

            ];
        }

        return $result;
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
