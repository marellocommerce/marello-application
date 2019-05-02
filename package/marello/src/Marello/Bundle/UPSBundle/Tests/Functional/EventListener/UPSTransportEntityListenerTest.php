<?php

namespace Marello\Bundle\UPSBundle\Tests\Functional\EventListener;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethod;
use Marello\Bundle\UPSBundle\Tests\Functional\DataFixtures\LoadShippingMethodsConfigsRules;

class UPSTransportEntityListenerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->loadFixtures(
            [
                LoadShippingMethodsConfigsRules::class
            ]
        );
    }

    public function testPostUpdate()
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        /** @var Channel $upsChannel */
        $upsChannel = $this->getReference('ups:channel_1');
        /** @var UPSSettings $upsTransport */
        $upsTransport = $upsChannel->getTransport();
        $applShipServices = $upsTransport->getApplicableShippingServices();
        /** @var ShippingService $toBeDeletedService */
        $toBeDeletedService = $applShipServices->first();

        $configuredMethods = $em
            ->getRepository('MarelloShippingBundle:ShippingMethodConfig')
            ->findBy([
                'method' => UPSShippingMethod::IDENTIFIER . '_' . $upsChannel->getId()]);

        $typesBefore = $em
            ->getRepository('MarelloShippingBundle:ShippingMethodTypeConfig')
            ->findBy(['methodConfig' => $configuredMethods, 'type' => $toBeDeletedService->getCode()]);

        static::assertNotEmpty($typesBefore);

        $upsTransport->removeApplicableShippingService($toBeDeletedService);
        $em->persist($upsTransport);
        $em->flush();

        $typesAfter = $em
            ->getRepository('MarelloShippingBundle:ShippingMethodTypeConfig')
            ->findBy(['methodConfig' => $configuredMethods, 'type' => $toBeDeletedService->getCode()]);

        static::assertEmpty($typesAfter);
    }
}
