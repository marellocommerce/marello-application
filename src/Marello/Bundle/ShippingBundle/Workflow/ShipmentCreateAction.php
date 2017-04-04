<?php

namespace Marello\Bundle\ShippingBundle\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Action\ActionInterface;
use Oro\Component\ConfigExpression\ContextAccessor;

use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;

class ShipmentCreateAction extends AbstractAction
{
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
        $service = $this->contextAccessor->getValue($context, $this->service);
        /** @var ShippingAwareInterface $entity */
        $entity = $this->contextAccessor->getValue($context, $this->entity);
        $entityClass = $this->doctrine->getEntityManager()->getClassMetadata(get_class($entity))->getName();

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
    }
}
