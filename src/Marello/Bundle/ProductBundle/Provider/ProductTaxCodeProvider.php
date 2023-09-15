<?php

namespace Marello\Bundle\ProductBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Provider\OrderItem\AbstractOrderItemFormChangesProvider;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ProductTaxCodeProvider extends AbstractOrderItemFormChangesProvider
{
    public function __construct(
        protected ManagerRegistry $registry,
        protected AclHelper $aclHelper
    ) {
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
        $products = $this->getRepository()->findBySalesChannel($salesChannel->getId(), $productIds, $this->aclHelper);

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
