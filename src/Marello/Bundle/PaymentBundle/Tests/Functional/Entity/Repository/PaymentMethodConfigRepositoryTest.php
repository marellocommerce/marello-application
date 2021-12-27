<?php

namespace Marello\Bundle\PaymentBundle\Tests\Functional\Entity\Repository;

use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodConfigRepository;
use Marello\Bundle\PaymentBundle\Tests\Functional\DataFixtures\LoadPaymentMethodConfigsWithFakeMethods;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class PaymentMethodConfigRepositoryTest extends WebTestCase
{
    /**
     * @var PaymentMethodConfigRepository
     */
    protected $repository;

    protected function setUp(): void
    {
        $this->initClient([], static::generateBasicAuthHeader());
        $this->loadFixtures([
            LoadPaymentMethodConfigsWithFakeMethods::class,
        ]);

        $this->repository = static::getContainer()->get('doctrine')
            ->getRepository('MarelloPaymentBundle:PaymentMethodConfig');
    }

    public function testDeleteByMethod()
    {
        $method = 'ups';

        static::assertNotEmpty($this->repository->findByMethod($method));

        $this->repository->deleteByMethod($method);

        static::assertEmpty($this->repository->findByMethod($method));
    }

    public function testDeleteMethodConfigByIds()
    {
        $ids = [
            $this->getReference('payment_rule.3.method_config_without_type_configs')->getId(),
        ];

        $this->repository->deleteByIds($ids);

        static::assertEmpty($this->repository->findBy(['id' => $ids]));
    }
}
