<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseGroupRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine\WarehouseListener;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class WarehouseListenerTest extends TestCase
{
    /**
     * @var WarehouseListener
     */
    private $warehouseListener;

    /**
     * @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $translator;

    /**
     * @var Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $session;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $aclHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->session = $this->createMock(Session::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $applicationState = $this->createMock(ApplicationState::class);
        $applicationState->expects($this->any())
            ->method('isInstalled')
            ->willReturn(true);
        $this->warehouseListener = new WarehouseListener(
            $applicationState,
            $this->translator,
            $this->session,
            $this->aclHelper
        );
    }

    /**
     * @dataProvider prePersistDataProvider
     * @param MockObject|null $warehouseGroup
     */
    public function testPrePersist(MockObject $warehouseGroup = null)
    {
        $warehouseType = new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL);
        $warehouse = new Warehouse();
        $warehouse->setWarehouseType($warehouseType);

        $repository = $this->createMock(WarehouseGroupRepository::class);
        $repository
            ->expects(static::once())
            ->method('findSystemWarehouseGroup')
            ->willReturn($warehouseGroup);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(static::once())
            ->method('getRepository')
            ->with(WarehouseGroup::class)
            ->willReturn($repository);

        /** @var LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject $args **/
        $args = $this
            ->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $this->warehouseListener->prePersist($warehouse, $args);

        static::assertEquals($warehouseGroup, $warehouse->getGroup());
    }

    /**
     * @return array
     */
    public function prePersistDataProvider()
    {
        return [
            'withSystemGroup' => [
                'group' => $this->createMock(WarehouseGroup::class),
            ],
            'noSystemGroup' => [
                'group' => null,
            ]
        ];
    }
}
