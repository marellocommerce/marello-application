<?php

namespace Marello\Bundle\InvoiceBundle\EventListener;

use Marello\Bundle\InvoiceBundle\Manager\CreditmemoManager;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Component\Action\Event\ExtendableActionEvent;

class ManualCreditmemoCreationListener
{
    /**
     * @var CreditmemoManager
     */
    protected $creditmemoManager;

    /**
     * @var bool
     */
    protected $stopPropagation = false;
    
    /**
     * @param CreditmemoManager $creditmemoManager
     * @param bool $stopPropagation
     */
    public function __construct(
        CreditmemoManager $creditmemoManager,
        $stopPropagation = false
    ) {
        $this->creditmemoManager = $creditmemoManager;
        $this->stopPropagation = $stopPropagation;
    }
    
    /**
     * @param ExtendableActionEvent $event
     */
    public function onCredited(ExtendableActionEvent $event)
    {
        if (!$this->isCorrectRefundContext($event->getContext())) {
            return;
        }

        /** @var Refund $entity */
        $entity = $event->getContext()->getData()->get('refund');
        $this->creditmemoManager->createCreditmemo($entity);

        if ($this->stopPropagation) {
            $event->stopPropagation();
        }
    }

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isCorrectRefundContext($context)
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has('refund')
            && $context->getData()->get('refund') instanceof Refund
        );
    }
}
