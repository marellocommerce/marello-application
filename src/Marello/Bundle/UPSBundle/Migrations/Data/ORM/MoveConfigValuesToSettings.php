<?php

namespace Marello\Bundle\UPSBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ManualShippingBundle\Migrations\Data\ORM\LoadManualShippingIntegration;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Marello\Bundle\ShippingBundle\Migrations\Data\ORM\AbstractMoveConfigValuesToSettings;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethod;
use Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config\ChannelByTypeFactory;
use Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config\UPSConfigFactory;
use Marello\Bundle\UPSBundle\Migrations\Data\ORM\Config\UPSConfigToSettingsConverter;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MoveConfigValuesToSettings extends AbstractMoveConfigValuesToSettings implements DependentFixtureInterface
{
    const SECTION_NAME = 'marello_shipping';
    const UPS_TYPE = 'marello_ups';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadManualShippingIntegration::class,
        ];
    }

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
        $this->upsConfigToSettingsConverter =
            new UPSConfigToSettingsConverter(
                $container->getParameter('marello_ups.api.url.production'),
                $container->get('doctrine'),
                $container->get('oro_security.encoder.default')
            );
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

        $this->addMethodConfigToDefaultShippingRule($manager, $upsChannel);
    }

    /**
     * @param Channel $upsChannel
     */
    protected function getDispatchShippingMethodRenamingEvent(Channel $upsChannel)
    {
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

    /**
     * @param ObjectManager $manager
     * @param Channel       $channel
     */
    private function addMethodConfigToDefaultShippingRule(ObjectManager $manager, Channel $channel)
    {
        $typeConfig = new ShippingMethodTypeConfig();
        $typeConfig->setEnabled(true);
        $typeConfig
            ->setType('11')
            ->setOptions([UPSShippingMethod::OPTION_SURCHARGE => 0.0]);

        $methodConfig = new ShippingMethodConfig();
        $methodConfig
            ->setMethod($this->getIdentifier($channel))
            ->setOptions([UPSShippingMethod::OPTION_SURCHARGE => 0.0])
            ->addTypeConfig($typeConfig);

        $defaultShippingRule = $this->getReference(LoadManualShippingIntegration::DEFAULT_RULE_REFERENCE);
        $defaultShippingRule->addMethodConfig($methodConfig);

        $manager->persist($defaultShippingRule);
        $manager->flush();
    }
    
    /**
     * @param Channel $channel
     *
     * @return int|string
     */
    private function getIdentifier(Channel $channel)
    {
        return $this->container
            ->get('marello_ups.method.identifier_generator.method')
            ->generateIdentifier($channel);
    }
}
