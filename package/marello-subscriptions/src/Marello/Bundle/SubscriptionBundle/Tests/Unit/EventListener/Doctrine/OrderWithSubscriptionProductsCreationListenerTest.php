<?php

namespace Marello\Bundle\SubscriptionBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SubscriptionBundle\Entity\Subscription;
use Marello\Bundle\SubscriptionBundle\EventListener\Doctrine\OrderWithSubscriptionProductsCreationListener;
use Marello\Bundle\SubscriptionBundle\Mapper\OrderToSubscriptionsMapper;
use PHPUnit\Framework\TestCase;

class OrderWithSubscriptionProductsCreationListenerTest extends TestCase
{
    /**
     * @var OrderToSubscriptionsMapper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderToSubscriptionsMapper;

    /**
     * @var OrderWithSubscriptionProductsCreationListener
     */
    protected $orderWithSubscriptionProductsCreationListener;

    protected function setUp()
    {
        $this->orderToSubscriptionsMapper = $this->getMockBuilder(OrderToSubscriptionsMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderWithSubscriptionProductsCreationListener = 
            new OrderWithSubscriptionProductsCreationListener($this->orderToSubscriptionsMapper);
    }

    public function testPostPersistHasSubscriptions()
    {
        $product = new Product();
        $product->setType('subscription');

        $orderItem = new OrderItem();
        $orderItem->setProduct($product);

        $order = new Order();
        $order->addItem($orderItem);

        $uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uow
            ->expects(static::exactly(2))
            ->method('scheduleForInsert');
        
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $em
            ->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        /** @var LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject $args **/
        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args
            ->expects(static::once())
            ->method('getEntity')
            ->willReturn($order);
        $args
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($em);

        $this->orderToSubscriptionsMapper
            ->expects(static::once())
            ->method('map')
            ->with($order)
            ->willReturn([new Subscription(), new Subscription()]);

        $this->orderWithSubscriptionProductsCreationListener->postPersist($args);
    }

    public function testPostPersistNoSubscriptions()
    {
        $product = new Product();
        $product->setType('simple');

        $orderItem = new OrderItem();
        $orderItem->setProduct($product);

        $order = new Order();
        $order->addItem($orderItem);

        /** @var LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject $args **/
        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args
            ->expects(static::once())
            ->method('getEntity')
            ->willReturn($order);
        $args
            ->expects(static::never())
            ->method('getEntityManager');

        $this->orderToSubscriptionsMapper
            ->expects(static::never())
            ->method('map');

        $this->orderWithSubscriptionProductsCreationListener->postPersist($args);
    }
}
