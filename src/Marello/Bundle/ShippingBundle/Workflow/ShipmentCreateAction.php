<?php

namespace Marello\Bundle\ShippingBundle\Workflow;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class ShipmentCreateAction extends AbstractAction
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;
    
    /**
     * @var ShippingMethodProviderInterface
     */
    protected $shippingMethodProvider;

    /**
     * @var ShippingContextInterface
     */
    protected $shippingContext;

    /**
     * @var PropertyPathInterface
     */
    protected $method;
    
    /**
     * @var PropertyPathInterface
     */
    protected $methodType;

    /**
     * @param ContextAccessor $contextAccessor
     * @param ManagerRegistry $doctrine
     * @param ShippingMethodProviderInterface $shippingMethodProvider
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        ManagerRegistry $doctrine,
        ShippingMethodProviderInterface $shippingMethodProvider
    ) {
        parent::__construct($contextAccessor);
        
        $this->doctrine = $doctrine;
        $this->shippingMethodProvider = $shippingMethodProvider;
    }
    
    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['context'])) {
            throw new InvalidParameterException('context parameter is required');
        }

        if (empty($options['method'])) {
            throw new InvalidParameterException('method parameter is required');
        }

        if (empty($options['methodType'])) {
            throw new InvalidParameterException('methodType parameter is required');
        }

        $this->shippingContext = $options['context'];
        $this->method = $options['method'];
        $this->methodType = $options['methodType'];

        return $this;
    }

    /**
     * @param mixed $context
     */
    protected function executeAction($context)
    {
        /** @var ShippingContextInterface[] $shippingContextArray */
        $shippingContextArray = $this->contextAccessor->getValue($context, $this->shippingContext);

        /** @var string $method */
        $method = $this->contextAccessor->getValue($context, $this->method);

        /** @var string $methodType */
        $methodType = $this->contextAccessor->getValue($context, $this->methodType);

        if ($shippingMethod = $this->shippingMethodProvider->getShippingMethod($method)) {
            if ($shippingMethodType = $shippingMethod->getType($methodType)) {
                $shipmentManager = $this->getShipmentManager();
                foreach ($shippingContextArray as $shippingContext) {
                    $shipment = $shippingMethodType->createShipment(
                        $shippingContext,
                        $shippingMethod->getLabel(),
                        $methodType
                    );
                    if ($shipment) {
                        $shipmentManager->persist($shipment);
                    }
                }
                $shipmentManager->flush();
            }
        }
    }
    
    /**
     * @return ObjectManager|null|object
     */
    private function getShipmentManager()
    {
        return $this->doctrine->getManagerForClass(Shipment::class);
    }
}
