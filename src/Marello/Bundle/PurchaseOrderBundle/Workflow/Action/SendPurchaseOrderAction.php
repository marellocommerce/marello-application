<?php

namespace Marello\Bundle\PurchaseOrderBundle\Workflow\Action;

use Marello\Bundle\NotificationBundle\Workflow\SendNotificationAction;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\SupplierBundle\Entity\Supplier;

class SendPurchaseOrderAction extends SendNotificationAction
{
    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        /** @var PurchaseOrder $entity */
        $entity     = $this->contextAccessor->getValue($context, $this->entity);
        $template   = $this->contextAccessor->getValue($context, $this->template);
        $recipients = $this->contextAccessor->getValue($context, $this->recipients);

        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }
        
        if ($entity->getSupplier()->getPoSendBy() === Supplier::SEND_PO_BY_EMAIL) {
            $this->sendProcessor->sendNotification($template, $recipients, $entity);
        }
    }
}
