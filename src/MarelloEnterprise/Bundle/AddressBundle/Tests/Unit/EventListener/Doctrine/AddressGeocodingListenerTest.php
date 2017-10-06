<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Tests\Unit\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use MarelloEnterprise\Bundle\AddressBundle\EventListener\Doctrine\AddressGeocodingListener;
use MarelloEnterprise\Bundle\GoogleApiBundle\Provider\GoogleApiResultsProviderInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResultInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class AddressGeocodingListenerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var GoogleApiResultsProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $geocodingApiResultsProvider;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var AddressGeocodingListener
     */
    protected $addressGeocodingListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->geocodingApiResultsProvider = $this->createMock(GoogleApiResultsProviderInterface::class);
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressGeocodingListener = new AddressGeocodingListener(
            $this->geocodingApiResultsProvider,
            $this->session
        );
    }

    /**
     * @dataProvider postUpdateDataProvider
     * @param array $changeSet
     * @param bool $isFeatureEnabled
     * @param GoogleApiResultInterface $apiResults
     * @param int $callTimes
     * @param int $callFlashBag
     */
    public function testPostUpdate(
        array $changeSet,
        $isFeatureEnabled,
        GoogleApiResultInterface $apiResults,
        $callTimes,
        $callFlashBag
    ) {
        /** @var MarelloAddress $address */
        $address = $this->getEntity(MarelloAddress::class, ['id' => 1]);

        /** @var LifecycleEventArgs|\PHPUnit_Framework_MockObject_MockObject $args **/
        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($em);
        $em->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);
        $uow->expects(static::once())
            ->method('getEntityChangeSet')
            ->with($address)
            ->willReturn($changeSet);
        /** @var FeatureChecker|\PHPUnit_Framework_MockObject_MockObject $featureChecker */
        $featureChecker = $this->getMockBuilder(FeatureChecker::class)->disableOriginalConstructor()->getMock();
        $featureChecker->expects(static::exactly($callTimes))
            ->method('isFeatureEnabled')
            ->willReturn($isFeatureEnabled);

        $this->addressGeocodingListener->setFeatureChecker($featureChecker);

        $this->geocodingApiResultsProvider
            ->expects(static::exactly($callTimes))
            ->method('getApiResults')
            ->willReturn($apiResults);

        $repository = $this->createMock(EntityRepository::class);
        $em
            ->expects(static::exactly($callTimes))
            ->method('getRepository')
            ->with(MarelloEnterpriseAddress::class)
            ->willReturn($repository);

        $eeAddress = $this->getEntity(MarelloEnterpriseAddress::class, ['id' => 1]);
        $repository->expects(static::exactly($callTimes))
            ->method('findOneBy')
            ->with(['address' => $address])
            ->willReturn($eeAddress);

        $em->expects(static::exactly($callTimes))
            ->method('persist')
            ->with($eeAddress);
        $em->expects(static::exactly($callTimes))
            ->method('flush');

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag->expects(static::exactly($callFlashBag))
            ->method('add');
        $this->session
            ->expects(static::exactly($callFlashBag))
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $this->addressGeocodingListener->postUpdate($address, $args);
    }

    public function postUpdateDataProvider()
    {
        return [
            [
                'changeSet' => [
                    'city' => ['oldValue' => 'oldCity', 'newValue' => 'newCity']
                ],
                'isFeatureEnabled' => true,
                'apiResults' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => true,
                    GoogleApiResult::FIELD_RESULT => [
                        GeocodingApiResultFactory::LATITUDE => 75,
                        GeocodingApiResultFactory::LONGITUDE => 75
                    ]
                ]),
                'callTimes' => 1,
                'callFlashBag' => 0
            ],
            [
                'changeSet' => [
                    'city' => ['oldValue' => 'oldCity', 'newValue' => 'newCity']
                ],
                'isFeatureEnabled' => true,
                'apiResults' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => false,
                    GoogleApiResult::FIELD_RESULT => [
                        GeocodingApiResultFactory::LATITUDE => 75,
                        GeocodingApiResultFactory::LONGITUDE => 75
                    ]
                ]),
                'callTimes' => 1,
                'callFlashBag' => 1
            ],
            [
                'changeSet' => [
                    'company' => ['oldValue' => 'oldCity', 'newValue' => 'newCity']
                ],
                'isFeatureEnabled' => true,
                'apiResults' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => true,
                    GoogleApiResult::FIELD_RESULT => [
                        GeocodingApiResultFactory::LATITUDE => 75,
                        GeocodingApiResultFactory::LONGITUDE => 75
                    ]
                ]),
                'callTimes' => 0,
                'callFlashBag' => 0
            ]
        ];
    }
}
