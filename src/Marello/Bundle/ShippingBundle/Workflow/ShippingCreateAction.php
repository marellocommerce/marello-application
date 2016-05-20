<?php

namespace Marello\Bundle\ShippingBundle\Workflow;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class ShippingCreateAction extends AbstractAction
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
        /** @var string $service */
        $service = ''; // TODO: Get service parameter.
        /** @var Order $order */
        $order = null; // TODO: Get order parameter.

        $dataFactory = $this->registry->getDataFactory($service);
        $integration = $this->registry->getIntegration($service);

        $data = $dataFactory->createData($order);

        /** @var Shipment $shipment */
        $shipment = $integration->requestShipment($data);

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
