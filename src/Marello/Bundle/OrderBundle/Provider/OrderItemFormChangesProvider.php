<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Marello\Bundle\OrderBundle\Provider\OrderItem\OrderItemDataProviderInterface;
use Symfony\Component\Form\FormInterface;

class OrderItemFormChangesProvider implements FormChangesProviderInterface
{
    const ITEMS_FIELD = 'items';
    const CHANNEL_FIELD = 'salesChannel';
 
    /**
     * @var OrderItemDataProviderInterface
     */
    protected $orderItemDataProvider;

    /**
     * @param OrderItemDataProviderInterface $orderItemDataProvider
     */
    public function __construct(OrderItemDataProviderInterface $orderItemDataProvider)
    {
        $this->orderItemDataProvider = $orderItemDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormChangesData(FormInterface $form, array $submittedData = null)
    {
        if ($form->has(self::ITEMS_FIELD) &&
            array_key_exists(self::CHANNEL_FIELD, $submittedData) &&
            array_key_exists(self::ITEMS_FIELD, $submittedData)) {
            $salesChannelId = (int)$submittedData[self::CHANNEL_FIELD];
            $productIds = [];
            foreach ($submittedData[self::ITEMS_FIELD] as $item) {
                $productIds[] = (int)$item['product'];
            }
            
            return $this->orderItemDataProvider->getData($salesChannelId, $productIds);
        }

        return null;
    }
}
