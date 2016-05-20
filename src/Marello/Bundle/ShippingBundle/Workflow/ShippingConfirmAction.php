<?php

namespace Marello\Bundle\ShippingBundle\Workflow;

use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class ShippingConfirmAction extends AbstractAction
{
    /** @var ShippingServiceRegistry */
    protected $registry;

    public function __construct(ContextAccessor $contextAccessor, ShippingServiceRegistry $registry)
    {
        parent::__construct($contextAccessor);

        $this->registry = $registry;
    }

    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        /** @var Shipment $shipment */
        $shipment = null;

        $integration = $this->registry->getIntegration($shipment->getShippingService());

        /** @var Shipment $shipment */
        $shipment = $integration->confirmShipment($shipment);

        $this->contextAccessor->setValue($context, 'attribute', $shipment);
    }

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     *
     * @return ActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        // TODO: Implement initialize() method.
    }
}
