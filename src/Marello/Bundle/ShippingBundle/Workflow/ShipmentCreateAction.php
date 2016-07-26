<?php

namespace Marello\Bundle\ShippingBundle\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class ShipmentCreateAction extends AbstractAction
{
    /** @var ShippingServiceRegistry */
    protected $registry;

    protected $order;

    /** @var PropertyPathInterface */
    protected $service;

    /** @var Registry */
    protected $doctrine;

    /**
     * ShipmentCreateAction constructor.
     *
     * @param ContextAccessor         $contextAccessor
     * @param ShippingServiceRegistry $registry
     * @param Registry                $doctrine
     */
    public function __construct(ContextAccessor $contextAccessor, ShippingServiceRegistry $registry, Registry $doctrine)
    {
        parent::__construct($contextAccessor);

        $this->registry = $registry;
        $this->doctrine = $doctrine;
    }

    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        /** @var string $service */
        $service = $this->contextAccessor->getValue($context, $this->service);
        /** @var Order $order */
        $order = $this->contextAccessor->getValue($context, $this->order);

        $dataFactory = $this->registry->getDataFactory($service);
        $integration = $this->registry->getIntegration($service);

        $data = $dataFactory->createData($order);

        $integration->createShipment($order, $data);
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
        if (empty($options['order']) || !($options['order'] instanceof PropertyPathInterface)) {
            throw new InvalidParameterException('Order parameter is required');
        }

        if (empty($options['service'])) {
            throw new InvalidParameterException('Service parameter is required');
        }

        $this->order = $options['order'];
        $this->service = $options['service'];
    }
}
