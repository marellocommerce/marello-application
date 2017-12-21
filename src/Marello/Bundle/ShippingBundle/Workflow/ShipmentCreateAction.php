<?php

namespace Marello\Bundle\ShippingBundle\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class ShipmentCreateAction extends AbstractAction
{
    const DEFAULT_SHIPPING_SERVICE = 'manual';

    /** @var ShippingServiceRegistry */
    protected $registry;

    protected $entity;

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
        $service = strtolower($this->contextAccessor->getValue($context, $this->service));
        /** @var ShippingAwareInterface $entity */
        $entity = $this->contextAccessor->getValue($context, $this->entity);
        $entityClass = $this->doctrine->getManager()->getClassMetadata(get_class($entity))->getName();

        if (!$service || !$this->registry->hasIntegration($service) || !$this->registry->hasDataFactory($service)) {
            $service = self::DEFAULT_SHIPPING_SERVICE;
        }

        $dataFactory = $this->registry->getDataFactory($service);
        $integration = $this->registry->getIntegration($service);
        $dataProvider = $this->registry->getDataProvider($entityClass);

        $data = $dataFactory->createData($dataProvider->setEntity($entity));

        $integration->createShipment($entity, $data);
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
        if (empty($options['entity']) || !($options['entity'] instanceof PropertyPathInterface)) {
            throw new InvalidParameterException('Entity parameter is required');
        }

        if (empty($options['service'])) {
            throw new InvalidParameterException('Service parameter is required');
        }

        $this->entity = $options['entity'];
        $this->service = $options['service'];

        return $this;
    }
}
