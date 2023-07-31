<?php

namespace Marello\Bundle\PaymentTermBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\PaymentTermBundle\Entity\MarelloPaymentTermSettings;
use Marello\Bundle\PaymentTermBundle\Integration\PaymentTermChannelType;
use Marello\Bundle\RuleBundle\Entity\Rule;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadPaymentTermIntegration extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    const DEFAULT_RULE_NAME = 'Default';
    const DEFAULT_RULE_REFERENCE = 'payment_rule.default';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganizationAndBusinessUnitData::class,
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

        $channel = $this->loadIntegration($manager);
        $paymentRule = $this->createDefaltPaymentRule($manager);
        $this->addMethodConfigToDefaultPaymentRule($channel, $paymentRule);

        $manager->flush();
    }

    private function createDefaltPaymentRule(ObjectManager $manager): PaymentMethodsConfigsRule
    {
        $rule = new Rule();
        $rule->setName(self::DEFAULT_RULE_NAME)
            ->setEnabled(true)
            ->setSortOrder(1);

        $paymentRule = new PaymentMethodsConfigsRule();

        $paymentRule->setRule($rule)
            ->setOrganization($this->getOrganization($manager))
            ->setCurrency('USD');

        $manager->persist($paymentRule);
        $this->addReference(self::DEFAULT_RULE_REFERENCE, $paymentRule);

        return $paymentRule;
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Channel
     */
    private function loadIntegration(ObjectManager $manager)
    {
        $label = (new LocalizedFallbackValue())->setString('Payment Term');

        $transport = new MarelloPaymentTermSettings();
        $transport->addLabel($label);

        $channel = new Channel();
        $channel->setType(PaymentTermChannelType::TYPE)
            ->setName('Payment Term')
            ->setEnabled(true)
            ->setOrganization($this->getOrganization($manager))
            ->setTransport($transport);

        $manager->persist($channel);
        $manager->flush();

        return $channel;
    }

    private function addMethodConfigToDefaultPaymentRule(
        Channel $channel,
        PaymentMethodsConfigsRule $paymentRule
    ) {
        $methodConfig = new PaymentMethodConfig();
        $methodConfig->setMethod($this->getIdentifier($channel));
        $paymentRule->addMethodConfig($methodConfig);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Organization|object
     */
    private function getOrganization(ObjectManager $manager)
    {
        if ($this->hasReference(LoadOrganizationAndBusinessUnitData::REFERENCE_DEFAULT_ORGANIZATION)) {
            return $this->getReference(LoadOrganizationAndBusinessUnitData::REFERENCE_DEFAULT_ORGANIZATION);
        } else {
            return $manager
                ->getRepository('OroOrganizationBundle:Organization')
                ->getFirst();
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
