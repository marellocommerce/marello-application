<?php

namespace Marello\Bundle\ShippingBundle\Workflow;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class ShippingConfirmAction extends AbstractAction
{
    /** @var ShippingServiceRegistry */
    protected $registry;

    /** @var PropertyPathInterface */
    protected $order;

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
        /** @var Order */
        $order = $this->contextAccessor->getValue($context, $this->order);

        /** @var Shipment $shipment */
        $shipment = $order->getShipment();

        $integration = $this->registry->getIntegration($shipment->getShippingService());

        /** @var Shipment $shipment */
        $shipment = $integration->confirmShipment($shipment);
    }

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     *
     * @return ActionInterface
     * @throws InvalidParameterException
     */

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
        if (empty($options['order']) || !($options['order'] instanceof PropertyPathInterface)) {
            throw new InvalidParameterException('Order parameter is required');
        }

        $this->order = $options['order'];
    }
}
