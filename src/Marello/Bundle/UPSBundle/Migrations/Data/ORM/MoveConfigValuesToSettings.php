<?php

namespace Marello\Bundle\UPSBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Marello\Bundle\ShippingBundle\Migrations\Data\ORM\AbstractMoveConfigValuesToSettings;
use Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config\ChannelByTypeFactory;
use Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config\UPSConfigFactory;
use Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config\UPSConfigToSettingsConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MoveConfigValuesToSettings extends AbstractMoveConfigValuesToSettings
{
    const SECTION_NAME = 'marello_shipping';

    const UPS_TYPE = 'ups';

    /**
     * @var ChannelByTypeFactory
     */
    protected $channelFromUPSConfigFactory;

    /**
     * @var UPSConfigFactory
     */
    protected $upsConfigFactory;

    /**
     * @var UPSConfigToSettingsConverter
     */
    protected $upsConfigToSettingsConverter;

    /**
     * @var IntegrationIdentifierGeneratorInterface
     */
    protected $shippingMethodIdentifierByChannelGenerator;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->channelFromUPSConfigFactory = $this->createChannelFromUPSConfigFactory($container);
        $this->upsConfigFactory = $this->createUPSConfigFactory($container);
        $this->upsConfigToSettingsConverter = new UPSConfigToSettingsConverter();
        $this->shippingMethodIdentifierByChannelGenerator = 
            $container->get('marello_ups.method.identifier_generator.method');
    }

    /**
     * {@inheritDoc}
     */
    protected function moveConfigFromSystemConfigToIntegration(
        ObjectManager $manager,
        OrganizationInterface $organization
    ) {
        $upsSystemConfig = $this->upsConfigFactory->createUPSConfig();

        $upsChannel = $this->channelFromUPSConfigFactory->createChannel(
            $organization,
            $this->upsConfigToSettingsConverter->convert($upsSystemConfig),
            $upsSystemConfig->isAllRequiredFieldsSet()
        );

        $manager->persist($upsChannel);
        $manager->flush();

        $this->getDispatchShippingMethodRenamingEvent($upsChannel);

        $manager->flush();
    }

    /**
     * @param Channel $upsChannel
     */
    protected function getDispatchShippingMethodRenamingEvent(Channel $upsChannel) {
        $this->dispatcher->dispatch(
            self::UPS_TYPE,
            $this->shippingMethodIdentifierByChannelGenerator->generateIdentifier($upsChannel)
        );
    }

    /**
     * @param ContainerInterface $container
     *
     * @return ChannelByTypeFactory
     */
    protected function createChannelFromUPSConfigFactory(ContainerInterface $container)
    {
        return new ChannelByTypeFactory(
            $container->get('marello_ups.provider.channel'),
            $container->get('translator')
        );
    }

    /**
     * @param ContainerInterface $container
     *
     * @return UPSConfigFactory
     */
    protected function createUPSConfigFactory(ContainerInterface $container)
    {
        return new UPSConfigFactory(
            $container->get('oro_config.manager')
        );
    }
}
