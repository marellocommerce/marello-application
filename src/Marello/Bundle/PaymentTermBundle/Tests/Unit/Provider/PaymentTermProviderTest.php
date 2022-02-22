<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Unit\Provider;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Marello\Bundle\PaymentTermBundle\Entity\Repository\PaymentTermRepository;
use Marello\Bundle\PaymentTermBundle\Provider\PaymentTermProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class PaymentTermProviderTest extends TestCase
{
    use EntityTrait;

    public function testGetDefaultPaymentTermWhenSet()
    {
        $configManager = $this->getConfigManagerMock('1');
        /** @var PaymentTermRepository|\PHPUnit\Framework\MockObject\MockObject $paymentTermRepository */
        $paymentTermRepository = $this->getPaymentTermRepositoryMock(
            static::once(),
            'find',
            '1',
            $this->getEntity(PaymentTerm::class, [
                'id' => 1,
                'code' => 'default',
                'term' => 14,
            ])
        );

        $doctrineHelper = $this->getDoctrineHelperMock($paymentTermRepository);

        $provider = new PaymentTermProvider($configManager, $doctrineHelper);

        $result = $provider->getDefaultPaymentTerm();

        static::assertInstanceOf(PaymentTerm::class, $result);
    }

    public function testGetDefaultPaymentTermWhenNotSet()
    {
        $configManager = $this->getConfigManagerMock(null);
        /** @var PaymentTermRepository|\PHPUnit\Framework\MockObject\MockObject $paymentTermRepository */
        $paymentTermRepository = $this->getPaymentTermRepositoryMock(static::never(), 'find');

        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper */
        $doctrineHelper = $this->getDoctrineHelperMock($paymentTermRepository);
        $provider = new PaymentTermProvider($configManager, $doctrineHelper);

        $result = $provider->getDefaultPaymentTerm();

        static::assertNull($result);
    }

    public function testGetPaymentTerms()
    {
        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager = $this->createMock(ConfigManager::class);

        $paymentTerms = [
            $this->getEntity(PaymentTerm::class, [
                'id' => 1,
                'code' => 'default',
                'term' => 14,
            ]),
            $this->getEntity(PaymentTerm::class, [
                'id' => 2,
                'code' => 'not default',
                'term' => 30,
            ]),
        ];

        /** @var PaymentTermRepository|\PHPUnit\Framework\MockObject\MockObject $paymentTermRepository */
        $paymentTermRepository = $this->getPaymentTermRepositoryMock(
            static::once(),
            'findAll',
            null,
            $paymentTerms
        );

        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper */
        $doctrineHelper = $this->getDoctrineHelperMock($paymentTermRepository);
        $provider = new PaymentTermProvider($configManager, $doctrineHelper);
        $result = $provider->getPaymentTerms();

        static::assertIsArray($result);
        static::assertCount(2, $result);
        foreach ($result as $item) {
            static::assertInstanceOf(PaymentTerm::class, $item);
        }
    }

    /**
     * Create PaymentTermRepository mock object based on 'fixed' parameters
     * @param $matcher
     * @param $method
     * @param null $argument
     * @param null $returnValue
     * @return PaymentTermRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPaymentTermRepositoryMock($matcher, $method, $argument = null, $returnValue = null)
    {
        /** @var PaymentTermRepository|\PHPUnit\Framework\MockObject\MockObject $paymentTermRepository */
        $paymentTermRepository = $this->createMock(PaymentTermRepository::class);
        $paymentTermRepository->expects($matcher)
            ->method($method)
            ->willReturn($returnValue)
        ;

        if ($argument) {
            $paymentTermRepository->with($argument);
        }

        return $paymentTermRepository;
    }

    /**
     * {@inheritdoc}
     * @param $value
     * @return ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConfigManagerMock($value)
    {
        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects(static::once())
            ->method('get')
            ->with('marello_payment_term.default_payment_term')
            ->willReturn($value)
        ;

        return $configManager;
    }

    /**
     * {@inheritdoc
     * @param $paymentTermRepository PaymentTermRepository|\PHPUnit\Framework\MockObject\MockObject
     * @return DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getDoctrineHelperMock($paymentTermRepository)
    {
        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper */
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper
            ->method('getEntityRepositoryForClass')
            ->with(PaymentTerm::class)
            ->willReturn($paymentTermRepository)
        ;

        return $doctrineHelper;
    }
}
