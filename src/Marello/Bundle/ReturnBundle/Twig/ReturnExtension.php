<?php

namespace Marello\Bundle\ReturnBundle\Twig;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Util\ReturnHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class ReturnExtension extends \Twig_Extension
{
    const NAME = 'marello_return';

    /** @var ReturnHelper */
    protected $returnHelper;

    /** @var WorkflowManager */
    protected $workflowManager;

    /**
     * ReturnExtension constructor.
     *
     * @param ReturnHelper $returnHelper
     * @param WorkflowManager $workflowManager
     */
    public function __construct(ReturnHelper $returnHelper, WorkflowManager $workflowManager)
    {
        $this->returnHelper = $returnHelper;
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
                'marello_return_get_order_item_returned_quantity',
                [$this, 'getOrderItemReturnedQuantity']
            ),
            new \Twig_SimpleFunction(
                'marello_return_is_on_hold',
                [$this, 'isOnHold']
            ),
        ];
    }

    /**
     * @param OrderItem $orderItem
     *
     * @return int
     */
    public function getOrderItemReturnedQuantity(OrderItem $orderItem)
    {
        return $this->returnHelper->getOrderItemReturnedQuantity($orderItem);
    }

    /**
     * @param ReturnEntity $returnEntity
     * @return bool
     */
    public function isOnHold(ReturnEntity $returnEntity)
    {
        $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($returnEntity);
        foreach ($workflowItems as $workflowItem) {
            if (strpos($workflowItem->getCurrentStep()->getName(), '_on_hold')) {
                return true;
            }
        }

        return false;
    }
}
