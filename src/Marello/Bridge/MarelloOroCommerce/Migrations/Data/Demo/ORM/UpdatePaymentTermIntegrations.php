<?php

namespace Marello\Bridge\MarelloOroCommerce\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentTermBundle\Entity\MarelloPaymentTermSettings;
use Marello\Bundle\PaymentTermBundle\Integration\PaymentTermChannelType;
use Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM\LoadPaymentTermIntegration;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\PaymentTermBundle\Migrations\Data\Demo\ORM\LoadPaymentRuleIntegrationData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdatePaymentTermIntegrations extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadPaymentRuleIntegrationData::class,
            LoadPaymentTermIntegration::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->container) {
            return;
        }

        if (!$this->container->hasParameter('oro_integration.entity.class')) {
            return;
        }

        $this->updateMethodConfigPaymentRules($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function updateMethodConfigPaymentRules(ObjectManager $manager)
    {
        $channels = $manager->getRepository(Channel::class)->findBy(['type' => PaymentTermChannelType::TYPE]);
        $methodConfigs = $manager->getRepository(PaymentMethodConfig::class)->findAll();
        $marelloPaymentTermChannel = null;
        $oroPaymentTermChannel = null;
        foreach ($channels as $channel) {
            if ($channel->getTransport() instanceof MarelloPaymentTermSettings) {
                $marelloPaymentTermChannel = $channel;
            } else {
                $oroPaymentTermChannel = $channel;
            }
        }
        if ($marelloPaymentTermChannel && $oroPaymentTermChannel) {
            foreach ($methodConfigs as $methodConfig) {
                if ($methodConfig->getMethod() === $this->getIdentifier($marelloPaymentTermChannel)) {
                    $methodConfig->setMethod($this->getIdentifier($oroPaymentTermChannel));
                    $manager->persist($methodConfig);
                }
            }
            $manager->flush();
            $manager->remove($marelloPaymentTermChannel);
            $manager->flush();
        }
    }

    /**
     * @param Channel $channel
     *
     * @return int|string
     */
    private function getIdentifier(Channel $channel)
    {
        return $this->container
            ->get('marello_payment_term.method.identifier_generator.method')
            ->generateIdentifier($channel);
    }
}
