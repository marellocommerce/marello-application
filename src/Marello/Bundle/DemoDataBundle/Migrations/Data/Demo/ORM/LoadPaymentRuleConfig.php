<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentTermBundle\Integration\PaymentTermChannelType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadPaymentRuleConfig extends AbstractFixture implements
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
            LoadPaymentRule::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $channel = $this->loadIntegration($manager);
        if (!$channel) {
            return;
        }

        $this->addMethodConfigToDefaultPaymentRule($manager, $channel);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Channel
     */
    private function loadIntegration(ObjectManager $manager)
    {
        $channels = $manager->getRepository(Channel::class)->findByType(PaymentTermChannelType::TYPE);
        return array_shift($channels);
    }

    /**
     * @param ObjectManager $manager
     * @param Channel       $channel
     */
    private function addMethodConfigToDefaultPaymentRule(ObjectManager $manager, Channel $channel)
    {
        foreach ($this->getPaymentRuleReferences() as $paymentRuleReference) {
            $methodConfig = new PaymentMethodConfig();
            $methodConfig->setMethod($this->getIdentifier($channel));

            $paymentRuleReference->addMethodConfig($methodConfig);
            $manager->persist($paymentRuleReference);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    protected function getPaymentRuleReferences()
    {
        return [
            $this->getReference(LoadPaymentRule::DEFAULT_RULE_REFERENCE_EUR),
            $this->getReference(LoadPaymentRule::DEFAULT_RULE_REFERENCE_GBP)
        ];
    }

    /**
     * Get organization
     * @param ObjectManager $manager
     * @return Organization
     */
    protected function getOrganization(ObjectManager $manager)
    {
        return $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
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
