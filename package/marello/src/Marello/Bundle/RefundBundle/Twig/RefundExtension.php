<?php

namespace Marello\Bundle\RefundBundle\Twig;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class RefundExtension extends \Twig_Extension
{
    const NAME = 'marello_refund';

    /** @var WorkflowManager */
    protected $workflowManager;

    /**
     * RefundExtension constructor.
     *
     * @param WorkflowManager $workflowManager
     */
    public function __construct(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * Refunds the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Refunds a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'marello_refund_is_pending',
                [$this, 'isPending']
            ),
        ];
    }

    /**
     * @param Refund $refund
     * @return bool
     */
    public function isPending(Refund $refund)
    {
        $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($refund);
        foreach ($workflowItems as $workflowItem) {
            if ('pending' === $workflowItem->getCurrentStep()->getName()) {
                return true;
            }
        }

        return false;
    }
}
