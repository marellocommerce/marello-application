<?php

namespace Marello\Bundle\OrderBundle\Twig;

use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class OrderExtension extends \Twig_Extension
{
    const NAME = 'marello_order';
    
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
                'marello_order_can_return',
                [$this, 'canReturn']
            )
        ];
    }

    /**
     * {@inheritdoc}
     * @param Order $order
     * @return boolean
     */
    public function canReturn(Order $order)
    {
        $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($order);
        foreach ($workflowItems as $workflowItem) {
            if ('shipped' === $workflowItem->getCurrentStep()->getName()) {
                return true;
            }
        }
        return false;
    }
}
