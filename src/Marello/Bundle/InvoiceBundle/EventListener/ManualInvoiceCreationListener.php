<?php

namespace Marello\Bundle\InvoiceBundle\EventListener;

use Marello\Bundle\InvoiceBundle\Manager\InvoiceManager;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Component\Action\Event\ExtendableActionEvent;

class ManualInvoiceCreationListener
{
    /**
     * @var InvoiceManager
     */
    protected $invoiceManager;

    /**
     * @var bool
     */
    protected $stopPropagation = false;
    
    /**
     * @param InvoiceManager $invoiceManager
     * @param bool $stopPropagation
     */
    public function __construct(
        InvoiceManager $invoiceManager,
        $stopPropagation = false
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->stopPropagation = $stopPropagation;
    }


    /**
     * @param ExtendableActionEvent $event
     */
    public function onInvoiced(ExtendableActionEvent $event)
    {
        if (!$this->isCorrectOrderContext($event->getContext())) {
            return;
        }

        /** @var Order $entity */
        $entity = $event->getContext()->getData()->get('order');
        $this->invoiceManager->createInvoice($entity);

        if ($this->stopPropagation) {
            $event->stopPropagation();
        }
    }

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isCorrectOrderContext($context)
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has('order')
            && $context->getData()->get('order') instanceof Order
        );
    }
}
