<?php

namespace Marello\Bundle\RefundBundle\Twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
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
     * @var Registry
     */
    protected $doctrine;

    /**
     * @param WorkflowManager $workflowManager
     * @param Registry $doctrine
     */
    public function __construct(WorkflowManager $workflowManager, Registry $doctrine)
    {
        $this->workflowManager = $workflowManager;
        $this->doctrine = $doctrine;
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
        $refundsForSameOrder = $this->doctrine
            ->getManagerForClass(Refund::class)
            ->getRepository(Refund::class)
            ->findBy(['order' => $refund->getOrder()]);
        $refundsAmount = 0.0;
        foreach ($refundsForSameOrder as $prevRefund) {
            $refundsAmount += $prevRefund->getRefundAmount();
        }
        
        return $refund->getOrder()->getGrandTotal() - $refundsAmount;
    }
}
