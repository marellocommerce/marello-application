<?php

namespace Marello\Bundle\UPSBundle\Tests\Functional\EventListener;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethod;

class UPSTransportEntityListenerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->loadFixtures(['Marello\Bundle\UPSBundle\Tests\Functional\DataFixtures\LoadShippingMethodsConfigsRules']);
    }

    public function testPostUpdate()
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        /** @var Channel $ups_channel */
        $ups_channel = $this->getReference('ups:channel_1');
        /** @var UPSSettings $ups_transport */
        $ups_transport = $ups_channel->getTransport();
        $applShipServices = $ups_transport->getApplicableShippingServices();
        /** @var ShippingService $toBeDeletedService */
        $toBeDeletedService = $applShipServices->first();

        $configuredMethods = $em
            ->getRepository('MarelloShippingBundle:ShippingMethodConfig')
            ->findBy([
                'method' => UPSShippingMethod::IDENTIFIER . '_' . $ups_channel->getId()]);
        $typesBefore = $em
            ->getRepository('MarelloShippingBundle:ShippingMethodTypeConfig')
            ->findBy(['methodConfig' => $configuredMethods, 'type' => $toBeDeletedService->getCode()]);

        static::assertNotEmpty($typesBefore);

        $ups_transport->removeApplicableShippingService($toBeDeletedService);
        $em->persist($ups_transport);
        $em->flush();

        $typesAfter = $em
            ->getRepository('MarelloShippingBundle:ShippingMethodTypeConfig')
            ->findBy(['methodConfig' => $configuredMethods, 'type' => $toBeDeletedService->getCode()]);

        static::assertEmpty($typesAfter);
    }
}
