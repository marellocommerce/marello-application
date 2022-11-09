<?php

namespace Marello\Bundle\PurchaseOrderBundle\Workflow\Action;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\NotificationBundle\Provider\EmailSendProcessor;
use Marello\Bundle\NotificationBundle\Workflow\SendNotificationAction;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Component\ConfigExpression\ContextAccessor;

class SendPurchaseOrderAction extends SendNotificationAction
{
    public function __construct(
        ContextAccessor $contextAccessor,
        EmailSendProcessor $sendProcessor,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct($contextAccessor, $sendProcessor);
    }

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

        foreach ($entity->getItems() as $item) {
            $item->setStatus(PurchaseOrderItem::STATUS_PENDING);
        }
        $this->entityManager->flush();
        
        if ($entity->getSupplier()->getPoSendBy() === Supplier::SEND_PO_BY_EMAIL) {
            $this->sendProcessor->sendNotification($template, $recipients, $entity);
        }
    }
}
