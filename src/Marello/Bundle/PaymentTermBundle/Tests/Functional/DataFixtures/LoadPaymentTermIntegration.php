<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\PaymentTermBundle\Entity\MarelloPaymentTermSettings;
use Marello\Bundle\PaymentTermBundle\Integration\PaymentTermChannelType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadPaymentTermIntegration extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const REFERENCE_PAYMENT_TERM = 'payment_term_integration';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $label = (new LocalizedFallbackValue())->setString('Payment Term');

        $transport = new MarelloPaymentTermSettings();
        $transport->addLabel($label);

        $channel = new Channel();
        $channel->setType(PaymentTermChannelType::TYPE)
            ->setName('Payment Term')
            ->setEnabled(true)
            ->setTransport($transport)
            ->setOrganization($this->getOrganization());

        $manager->persist($channel);
        $manager->flush();

        $this->setReference(self::REFERENCE_PAYMENT_TERM, $channel);
    }


    /**
     * @return Organization
     */
    private function getOrganization()
    {
        return $this->container->get('doctrine')
            ->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();
    }
}
