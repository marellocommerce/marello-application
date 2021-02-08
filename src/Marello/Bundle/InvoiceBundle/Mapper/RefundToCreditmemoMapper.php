<?php

namespace Marello\Bundle\InvoiceBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InvoiceBundle\Entity\Creditmemo;
use Marello\Bundle\InvoiceBundle\Entity\CreditmemoItem;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;

class RefundToCreditmemoMapper extends AbstractInvoiceMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($sourceEntity)
    {
        if (!($sourceEntity instanceof Refund)) {
            throw new \InvalidArgumentException(
                sprintf('Wrong source entity "%s" provided to RefundToCreditmemoMapper', get_class($sourceEntity))
            );
        }

        /** @var Refund $sourceEntity */
        $creditmemo = new Creditmemo();
        $data = $this->getData($sourceEntity->getOrder(), Creditmemo::class);
        $data['order'] = $sourceEntity->getOrder();
        $data['items'] = $this->getItems($sourceEntity->getItems());
        $subtotal = 0.00;
        $totalTax = 0.00;
        /** @var CreditmemoItem $item */
        foreach ($data['items'] as $item) {
            $subtotal += $item->getRowTotalExclTax();
            $totalTax += $item->getTax();
        }
        $data['subtotal'] = $subtotal;
        $data['totalTax'] = $totalTax;
        $data['grandTotal'] = $subtotal + $totalTax + $data['shippingAmountInclTax'];
        if ($data['invoicedAt'] === null) {
            $data['invoicedAt'] = new \DateTime('now', new \DateTimeZone('UTC'));
        }

        $this->assignData($creditmemo, $data);

        return $creditmemo;
    }

    /**
     * @param Collection $items
     * @return ArrayCollection
     */
    protected function getItems(Collection $items)
    {
        $refundItems = $items->toArray();
        $creditmemoItems = [];
        /** @var RefundItem $item */
        foreach ($refundItems as $item) {
            if (!$item->getOrderItem()) {
                continue;
            }
            $creditmemoItems[] = $this->mapItem($item);
        }

        return new ArrayCollection($creditmemoItems);
    }

    /**
     * @param RefundItem $refundItem
     * @return CreditmemoItem|null
     */
    protected function mapItem(RefundItem $refundItem)
    {
        $creditmemoItem = new CreditmemoItem();
        $orderItem = $refundItem->getOrderItem();
        $creditmemoItemData = $this->getData($orderItem, CreditmemoItem::class);
        $invoiceItemData['productUnit'] = $orderItem->getProductUnit() ? $orderItem->getProductUnit()->getId() : null;
        $quantity = $refundItem->getQuantity();
        $tax = $creditmemoItemData['tax'] / $creditmemoItemData['quantity'] * $quantity;

        $rowTotalExclTax = $creditmemoItemData['rowTotalExclTax'] / $creditmemoItemData['quantity'] * $quantity;
        $rowTotalInclTax = $creditmemoItemData['rowTotalInclTax'] / $creditmemoItemData['quantity'] * $quantity;

        $creditmemoItemData['quantity'] = $quantity;
        $creditmemoItemData['rowTotalExclTax'] = $rowTotalExclTax;
        $creditmemoItemData['rowTotalInclTax'] = $rowTotalInclTax;
        $creditmemoItemData['tax'] = $tax;
        $this->assignData($creditmemoItem, $creditmemoItemData);

        return $creditmemoItem;
    }
}
