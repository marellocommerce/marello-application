<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Unit\Provider;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Marello\Bundle\PaymentTermBundle\Provider\PaymentTermDeletePermissionProvider;
use Marello\Bundle\PaymentTermBundle\Provider\PaymentTermProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class PaymentTermDeletePermissionProviderTest extends TestCase
{
    use EntityTrait;

    protected $provider;

    public function setUp(): void
    {
        $paymentTerm = $this->getEntity(PaymentTerm::class, [
            'id' => 1,
            'code' => 'default',
            'term' => 14
        ]);

        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper */
        $doctrineHelper = $this->createMock(DoctrineHelper::class);

        /** @var PaymentTermProvider|\PHPUnit\Framework\MockObject\MockObject $paymentTermProvider */
        $paymentTermProvider = $this->createMock(PaymentTermProvider::class);
        $paymentTermProvider->expects(static::once())
            ->method('getDefaultPaymentTerm')
            ->with()
            ->willReturn($paymentTerm)
        ;

        $this->provider = new PaymentTermDeletePermissionProvider($doctrineHelper, $paymentTermProvider);
    }

    /**
     * @param PaymentTerm $paymentTerm
     * @param bool $expectedValue
     * @dataProvider isDeleteAllowedDataProvider
     */
    public function testIsDeleteAllowed(PaymentTerm $paymentTerm, bool $expectedValue)
    {
        static::assertEquals($this->provider->isDeleteAllowed($paymentTerm), $expectedValue);
    }

    public function isDeleteAllowedDataProvider()
    {
        return [
            'delete_allowed' => [
                'paymentTerm' => $this->getEntity(PaymentTerm::class, [
                    'id' => 2,
                    'code' => 'not default',
                    'term' => 30,
                ]),
                'expectedValue' => true,
            ],
            'delete_not_allowed' => [
                'paymentTerm' => $this->getEntity(PaymentTerm::class, [
                    'id' => 1,
                    'code' => 'default',
                    'term' => 14,
                ]),
                'expectedValue' => false,
            ],
        ];
    }
}
