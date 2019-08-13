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
        $doctrineHelper = $this->getDoctrineHelperMock('1', $this->getEntity(PaymentTerm::class, [
            'id' => 1,
            'code' => 'default',
            'term' => 14,
        ]));

        $provider = new PaymentTermProvider($configManager, $doctrineHelper);

        $result = $provider->getDefaultPaymentTerm();

        static::assertInstanceOf(PaymentTerm::class, $result);
    }

    public function testGetDefaultPaymentTermWhenNotSet()
    {
        $configManager = $this->getConfigManagerMock(null);
        $doctrineHelper = $this->getDoctrineHelperMock(null, null);

        $provider = new PaymentTermProvider($configManager, $doctrineHelper);

        $result = $provider->getDefaultPaymentTerm();

        static::assertNull($result);
    }

    public function testGetPaymentTerms()
    {
        /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject $configManager */
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

        /** @var PaymentTermRepository|\PHPUnit_Framework_MockObject_MockObject $paymentTermRepository */
        $paymentTermRepository = $this->createMock(PaymentTermRepository::class);
        $paymentTermRepository->expects(static::once())
            ->method('findAll')
            ->willReturn($paymentTerms)
        ;

        /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject $doctrineHelper */
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper
            ->method('getEntityRepositoryForClass')
            ->with(PaymentTerm::class)
            ->willReturn($paymentTermRepository)
        ;

        $provider = new PaymentTermProvider($configManager, $doctrineHelper);

        $result = $provider->getPaymentTerms();

        static::assertInternalType('array', $result);
        static::assertCount(2, $result);
        foreach ($result as $item) {
            static::assertInstanceOf(PaymentTerm::class, $item);
        }
    }

    protected function getConfigManagerMock($value)
    {
        /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject $configManager */
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects(static::once())
            ->method('get')
            ->with('marello_payment_term.default_payment_term')
            ->willReturn($value)
        ;

        return $configManager;
    }

    protected function getDoctrineHelperMock($argument, $result)
    {
        /** @var PaymentTermRepository|\PHPUnit_Framework_MockObject_MockObject $paymentTermRepository */
        $paymentTermRepository = $this->createMock(PaymentTermRepository::class);
        $paymentTermRepository->expects(static::once())
            ->method('find')
            ->with($argument)
            ->willReturn($result)
        ;

        /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject $doctrineHelper */
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper
            ->method('getEntityRepositoryForClass')
            ->with(PaymentTerm::class)
            ->willReturn($paymentTermRepository)
        ;

        return $doctrineHelper;
    }
}
