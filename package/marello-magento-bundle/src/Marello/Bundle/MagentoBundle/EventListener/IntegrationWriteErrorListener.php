<?php

namespace Marello\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Event\WriterErrorEvent;
use Marello\Bundle\MagentoBundle\Entity\Order;

class IntegrationWriteErrorListener
{
    /**
     * @param WriterErrorEvent $event
     */
    public function handleError(WriterErrorEvent $event)
    {
        $warning = '';
        $items   = $event->getBatchItems();

        switch ($event->getJobName()) {
            case 'mage_order_import':
                $entity = 'orders';
                $ids    = array_map(
                    function (Order $item) {
                        return $item->getIncrementId();
                    },
                    $items
                );

                break;
            default:
                return;
        }

        $warning .= sprintf('Following %s were not imported: %s', $entity, implode(', ', $ids));

        $event->addWarningText($warning);
        $event->setCouldBeSkipped(true);
    }
}
