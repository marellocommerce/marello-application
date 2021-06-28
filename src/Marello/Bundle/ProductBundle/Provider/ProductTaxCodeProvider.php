<?php

namespace Marello\Bundle\ProductBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Provider\OrderItem\AbstractOrderItemFormChangesProvider;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class ProductTaxCodeProvider extends AbstractOrderItemFormChangesProvider
{
    /**
     * @var Registry $registry
     */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
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
            if ($taxCode) {
                $data[sprintf('%s%s', self::IDENTIFIER_PREFIX, $product->getId())] = [
                    'id' => $taxCode->getId(),
                    'code' => $taxCode->getCode()
                ];
            }
        }
        if (!empty($data)) {
            $result = $context->getResult();
            $result[self::ITEMS_FIELD]['tax_code'] = $data;
            $context->setResult($result);
        }
    }

    /**
     * @return ProductRepository
     */
    protected function getRepository()
    {
        return $this->registry->getManagerForClass(Product::class)->getRepository(Product::class);
    }
}
