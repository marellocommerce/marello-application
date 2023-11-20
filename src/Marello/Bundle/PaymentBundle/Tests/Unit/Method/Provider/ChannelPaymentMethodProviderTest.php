<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Method\Provider;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Marello\Bundle\PaymentBundle\Method\Factory\IntegrationPaymentMethodFactoryInterface;
use Marello\Bundle\PaymentBundle\Method\Provider\Integration\ChannelPaymentMethodProvider;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Component\Testing\Unit\EntityTrait;

class ChannelPaymentMethodProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @internal
     */
    const TYPE = 'custom_type';

    use EntityTrait;

    /**
     * @var ChannelPaymentMethodProvider
     */
    private $provider;

    /**
     * @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $doctrineHelper;

    /**
     * @var IntegrationPaymentMethodFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $methodFactory;

    /**
     * @var PaymentMethodInterface
     */
    private $enabledMethod;

    /**
     * @var PaymentMethodInterface
     */
    private $disabledMethod;

    public function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $repository = $this->createMock(ChannelRepository::class);

        $this->doctrineHelper
            ->method('getEntityRepository')
            ->with('OroIntegrationBundle:Channel')
            ->willReturn($repository);

        $loadedChannel = $this->createChannel('ch_enabled');
        $fetchedChannel = $this->createChannel('ch_disabled');

        $this->enabledMethod = $this->createMock(PaymentMethodInterface::class);
        $this->enabledMethod
            ->method('getIdentifier')
            ->willReturn('ups_10');

        $this->disabledMethod = $this->createMock(PaymentMethodInterface::class);
        $this->disabledMethod
            ->method('getIdentifier')
            ->willReturn('ups_20');

        $this->methodFactory = $this->createMock(IntegrationPaymentMethodFactoryInterface::class);
        $this->methodFactory
            ->method('create')
            ->will($this->returnValueMap([
                [$loadedChannel, $this->enabledMethod],
                [$fetchedChannel, $this->disabledMethod],
            ]));

        $this->provider = new ChannelPaymentMethodProvider(static::TYPE, $this->doctrineHelper, $this->methodFactory);

        $doctrineEvent = $this->createLifecycleEventArgsMock();
        $this->provider->postLoad($loadedChannel, $doctrineEvent);

        $query = $this->createMock(AbstractQuery::class);
        $query->method('getResult')->willReturn([$fetchedChannel]);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('expr')->willReturn(new Expr());
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $repository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
    }

    public function testGetPaymentMethods()
    {
        $methods = $this->provider->getPaymentMethods();
        static::assertCount(2, $methods);
        $actualMethod = reset($methods);
        static::assertSame($this->enabledMethod, $actualMethod);
    }

    public function testGetPaymentMethod()
    {
        $method = $this->provider->getPaymentMethod($this->enabledMethod->getIdentifier());
        static::assertInstanceOf(PaymentMethodInterface::class, $method);
    }

    public function testHasPaymentMethod()
    {
        static::assertTrue($this->provider->hasPaymentMethod($this->enabledMethod->getIdentifier()));
    }

    public function testHasPaymentMethodFalse()
    {
        static::assertFalse($this->provider->hasPaymentMethod('wrong'));
    }

    /**
     * @param string $name
     *
     * @return Channel
     */
    private function createChannel($name)
    {
        return $this->getEntity(
            Channel::class,
            ['id' => 20, 'name' => $name, 'type' => static::TYPE]
        );
    }

    /**
     * @return LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createLifecycleEventArgsMock()
    {
        return $this->createMock(LifecycleEventArgs::class);
    }
}
