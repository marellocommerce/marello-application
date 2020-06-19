<?php

namespace Marello\Bundle\RefundBundle\Twig;

use Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RefundExtension extends AbstractExtension
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
     * RefundExtension constructor.
     * @param WorkflowManager $workflowManager
     * @param RefundBalanceCalculator $balanceCalculator
     */
    public function __construct(
        WorkflowManager $workflowManager,
        RefundBalanceCalculator $balanceCalculator
    ) {
        $this->workflowManager = $workflowManager;
        $this->refundBalanceCalculator = $balanceCalculator;
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
            new TwigFunction(
                'marello_refund_is_pending',
                [$this, 'isPending']
            ),
            new TwigFunction(
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
}
