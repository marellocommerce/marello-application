<?php

namespace Marello\Bundle\UPSBundle\EventListener;

use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Marello\Bundle\ShippingBundle\Method\Event\MethodTypeRemovalEventDispatcherInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Method\Identifier\UPSMethodTypeIdentifierGeneratorInterface;
use Marello\Bundle\UPSBundle\Provider\ChannelType;

class UPSTransportEntityListener
{
    /**
     * @var IntegrationIdentifierGeneratorInterface
     */
    private $integrationIdentifierGenerator;

    /**
     * @var UPSMethodTypeIdentifierGeneratorInterface
     */
    private $typeIdentifierGenerator;

    /**
     * @var MethodTypeRemovalEventDispatcherInterface
     */
    private $typeRemovalEventDispatcher;

    /**
     * @param IntegrationIdentifierGeneratorInterface   $integrationIdentifierGenerator
     * @param UPSMethodTypeIdentifierGeneratorInterface $typeIdentifierGenerator
     * @param MethodTypeRemovalEventDispatcherInterface $typeRemovalEventDispatcher
     */
    public function __construct(
        IntegrationIdentifierGeneratorInterface $integrationIdentifierGenerator,
        UPSMethodTypeIdentifierGeneratorInterface $typeIdentifierGenerator,
        MethodTypeRemovalEventDispatcherInterface $typeRemovalEventDispatcher
    ) {
        $this->integrationIdentifierGenerator = $integrationIdentifierGenerator;
        $this->typeIdentifierGenerator = $typeIdentifierGenerator;
        $this->typeRemovalEventDispatcher = $typeRemovalEventDispatcher;
    }

    /**
     * @param UPSSettings $transport
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(UPSSettings $transport, LifecycleEventArgs $args)
    {
        /** @var PersistentCollection $services */
        $services = $transport->getApplicableShippingServices();
        $deletedServices = $services->getDeleteDiff();
        if (0 !== count($deletedServices)) {
            $entityManager = $args->getObjectManager();
            $channel = $entityManager
                ->getRepository('OroIntegrationBundle:Channel')
                ->findOneBy(['type' => ChannelType::TYPE, 'transport' => $transport->getId()]);

            if (null !== $channel) {
                foreach ($deletedServices as $deletedService) {
                    $methodId = $this->integrationIdentifierGenerator->generateIdentifier($channel);
                    $typeId = $this->typeIdentifierGenerator->generateIdentifier($channel, $deletedService);
                    $this->typeRemovalEventDispatcher->dispatch($methodId, $typeId);
                }
            }
        }
    }
}
