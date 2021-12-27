<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Method\EventListener;

use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodConfigRepository;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Method\Event\MethodRenamingEvent;
use Marello\Bundle\PaymentBundle\Method\EventListener\MethodRenamingListener;

class MethodRenamingListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentMethodConfigRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMethodConfigRepository;

    /**
     * @var MethodRenamingListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->paymentMethodConfigRepository = $this->createMock(PaymentMethodConfigRepository::class);
        $this->listener = new MethodRenamingListener($this->paymentMethodConfigRepository);
    }

    public function testOnMethodRename()
    {
        $oldId = 'old_name';
        $newId = 'new_name';

        /** @var MethodRenamingEvent|\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->createMock(MethodRenamingEvent::class);
        $event->expects(static::any())
            ->method('getOldMethodIdentifier')
            ->willReturn($oldId);

        $event->expects(static::any())
            ->method('getNewMethodIdentifier')
            ->willReturn($newId);

        $config1 = $this->createMock(PaymentMethodConfig::class);
        $config1->expects(static::once())
            ->method('setMethod')
            ->with($newId);
        $config2 = $this->createMock(PaymentMethodConfig::class);
        $config2->expects(static::once())
            ->method('setMethod')
            ->with($newId);

        $this->paymentMethodConfigRepository->expects(static::once())
            ->method('findByMethod')
            ->with($oldId)
            ->willReturn([$config1, $config2]);

        $this->listener->onMethodRename($event);
    }
}
