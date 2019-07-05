<?php

namespace Marello\Bundle\RefundBundle\Twig;

use Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class RefundExtension extends \Twig_Extension
{
    const NAME = 'marello_refund';

    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @var RefundBalanceCalculator
     */
    protected $refundBalanceCalculator;

    /**
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
            new \Twig_SimpleFunction(
                'marello_refund_get_balance',
                [$this, 'getBalance']
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

    /**
     * @param Refund $refund
     * @return float
     */
    public function getBalance(Refund $refund)
    {
        return $this->refundBalanceCalculator->caclulateBalance($refund);
    }

    /**
     * Add refund calculator via call method on service in order to keep BC
     * should be removed for 3.0
     * @deprecated remove in 3.0
     * @param RefundBalanceCalculator $balanceCalculator
     * @return $this
     */
    public function setRefundBalanceCalculator(RefundBalanceCalculator $balanceCalculator)
    {
        $this->refundBalanceCalculator = $balanceCalculator;

        return $this;
    }
}
