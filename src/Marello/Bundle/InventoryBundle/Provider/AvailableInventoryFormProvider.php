<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\OrderBundle\Provider\OrderItem\AbstractOrderItemFormChangesProvider;

class AvailableInventoryFormProvider extends AbstractOrderItemFormChangesProvider
{
    /** @var AvailableInventoryProvider $availableInventoryProvider */
    protected $availableInventoryProvider;

    /**
     * {@inheritdoc}
     * @param AvailableInventoryProvider $availableInventoryProvider
     */
    public function __construct(AvailableInventoryProvider $availableInventoryProvider)
    {
        $this->availableInventoryProvider = $availableInventoryProvider;
    }

    /**
     * {@inheritdoc}
     * @param FormChangeContextInterface $context
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
        $requestedQuantities = [];
        foreach ($submittedData[self::ITEMS_FIELD] as $item) {
            $productIds[] = (int)$item['product'];
            $requestedQuantities[$this->getIdentifier((int)$item['product'])] = (int)$item['quantity'];
        }

        $data = [];
        $products = $this->availableInventoryProvider->getProducts($salesChannel->getId(), $productIds);

        /** @var Product $product */
        foreach ($products as $product) {
            $availableInventory = $this->availableInventoryProvider->getAvailableInventory($product, $salesChannel);
            $productIdentifier = $this->getIdentifier($product->getId());
            $data[$productIdentifier]['value'] = $availableInventory;

        }

        $result = $context->getResult();
        $result[self::ITEMS_FIELD]['inventory'] = $data;
        $context->setResult($result);
    }

    protected function isValidRequestedQuantity($productIdentifier, $requestedQuantities, $availableInventory)
    {
        $requestedQuantities[$productIdentifier];
        if (!array_key_exists($productIdentifier, $requestedQuantities)) {
            return false;
        }

        return ($requestedQuantities[$productIdentifier] <= (int)$availableInventory);
    }


    /**
     * Get entity identifier by combining the id and prefix
     * @param int $id
     * @return string
     */
    protected function getIdentifier($id)
    {
        return sprintf('%s%s', self::IDENTIFIER_PREFIX, $id);
    }
}
