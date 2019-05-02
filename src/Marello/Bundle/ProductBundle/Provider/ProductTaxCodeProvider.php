<?php

namespace Marello\Bundle\ProductBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Provider\OrderItem\AbstractOrderItemFormChangesProvider;
use Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDataProviderInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class ProductTaxCodeProvider extends AbstractOrderItemFormChangesProvider
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
        $order = $context->getForm()->getData();
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
        /** @var Product[] $products */
        $products = $this->getRepository()->findBySalesChannel($salesChannel->getId(), $productIds);

        foreach ($products as $product) {
            $taxCode = $product->getSalesChannelTaxCode($salesChannel) ? : $product->getTaxCode();
            $data[sprintf('%s%s', self::IDENTIFIER_PREFIX, $product->getId())] = [
                'id' => $taxCode->getId(),
                'code' => $taxCode->getCode(),

            ];
        }

        $result = $context->getResult();
        $result[self::ITEMS_FIELD]['tax_code'] = $data;
        $context->setResult($result);
    }

    /**
     * @return ProductRepository
     */
    protected function getRepository()
    {
        return $this->registry->getManagerForClass(Product::class)->getRepository(Product::class);
    }
}
