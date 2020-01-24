<?php

namespace Marello\Bundle\PurchaseOrderBundle\Twig;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class PurchaseOrderExtension extends \Twig_Extension
{
    const NAME = 'marello_purchaseorder';
    
    /** @var WorkflowManager */
    protected $workflowManager;

    /**
     * ProductExtension constructor.
     *
     * @param WorkflowManager $workflowManager
     */
    public function __construct(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'marello_purchaseorder_can_edit',
                [$this, 'canEdit']
            )
        ];
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     *
     * @return boolean
     */
    public function canEdit(PurchaseOrder $purchaseOrder)
    {
        $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($purchaseOrder);
        foreach ($workflowItems as $workflowItem) {
            if ('not_sent' === $workflowItem->getCurrentStep()->getName()) {
                return true;
            }
        }

        return false;
    }
}
