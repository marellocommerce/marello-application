<?php

namespace Marello\Bundle\PricingBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Provider\OrderItem\AbstractOrderItemFormChangesProvider;
use Marello\Bundle\PricingBundle\Entity\BasePrice;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\PricingBundle\Entity\Repository\ProductChannelPriceRepository;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

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
            $productId = $item->getProduct()->getId();
            if (isset($data[$this->getIdentifier($productId)])) {
                $item->setPrice($data[$this->getIdentifier($productId)]['value']);
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
        $price = $this->getProductChannelPriceRepository()->findOneBySalesChannel(
            $channel->getId(),
            $product->getId()
        );
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
     * @param SalesChannel $channel
     * @param Product $product
     * @return float
     */
    public function getDefaultPrice($channel, $product)
    {
        $currency = $channel->getCurrency();
        $price = $this->getProductPriceRepository()->findOneBy(
            ['product' => $product->getId(), 'currency' => $currency]
        );

        return $price instanceof BasePrice ? (float)$price->getValue() : null;
    }

    /**
     * @return ProductRepository
     */
    protected function getProductRepository()
    {
        return $this->getRepository(Product::class);
    }

    /**
     * @return EntityRepository
     */
    protected function getProductPriceRepository()
    {
        return $this->getRepository(ProductPrice::class);
    }

    /**
     * @return ProductChannelPriceRepository
     */
    protected function getProductChannelPriceRepository()
    {
        return $this->getRepository(ProductChannelPrice::class);
    }

    /**
     * @param string $className
     * @return EntityRepository
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
