<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Method;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\AddressBundle\Tests\Stubs\AddressStub;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Doctrine\DoctrineShippingLineItemCollection;
use Marello\Bundle\ShippingBundle\Context\ShippingContext;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Factory\PriceRequestFactory;
use Marello\Bundle\UPSBundle\Model\Package;
use Marello\Bundle\UPSBundle\Model\Request\PriceRequest;
use Oro\Component\Testing\Unit\EntityTrait;

class PriceRequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var UPSSettings|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transport;

    /**
     * @var ShippingService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingService;

    /**
     * @var PriceRequestFactory
     */
    protected $priceRequestFactory;

    /**
     * @var SymmetricCrypterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $symmetricCrypter;

    protected function setUp()
    {
        $this->shippingService = $this->createMock(ShippingService::class);

        $this->transport = $this->getEntity(
            UPSSettings::class,
            [
                'upsApiUser' => 'some user',
                'upsApiPassword' => 'some password',
                'upsApiKey' => 'some key',
                'upsShippingAccountNumber' => 'some number',
                'upsShippingAccountName' => 'some name',
                'upsPickupType' => '01',
                'upsCountry' => new Country('US'),
                'applicableShippingServices' => [new ShippingService()]
            ]
        );

        $this->symmetricCrypter = $this
            ->getMockBuilder(SymmetricCrypterInterface::class)
            ->getMock();

        $this->priceRequestFactory = new PriceRequestFactory(
            $this->symmetricCrypter
        );
    }

    /**
     * @param int $lineItemCnt
     * @param int $productWeight
     * @param string $unitOfWeight
     * @param PriceRequest|null $expectedRequest
     *
     * @dataProvider packagesDataProvider
     */
    public function testCreate($lineItemCnt, $productWeight, $unitOfWeight, $expectedRequest)
    {
        $this->symmetricCrypter
            ->expects($this->once())
            ->method('decryptData')
            ->with('some password')
            ->willReturn('some password');

        $this->transport->setUpsUnitOfWeight($unitOfWeight);

        $lineItems = [];
        $allProductsShippingOptions = [];
        for ($i = 1; $i <= $lineItemCnt; $i++) {
            /** @var Product $product */
            $product = $this->getEntity(Product::class, ['id' => $i]);

            $lineItems[] = new ShippingLineItem([
                ShippingLineItem::FIELD_PRODUCT => $product,
                ShippingLineItem::FIELD_QUANTITY => 1,
                ShippingLineItem::FIELD_ENTITY_IDENTIFIER => 1,
                ShippingLineItem::FIELD_WEIGHT => $productWeight
            ]);
        }

        $context = new ShippingContext([
            ShippingContext::FIELD_LINE_ITEMS => new DoctrineShippingLineItemCollection($lineItems),
            ShippingContext::FIELD_BILLING_ADDRESS => new AddressStub(),
            ShippingContext::FIELD_SHIPPING_ORIGIN => new AddressStub(),
            ShippingContext::FIELD_SHIPPING_ADDRESS => new AddressStub(),
            ShippingContext::FIELD_PAYMENT_METHOD => '',
            ShippingContext::FIELD_CURRENCY => 'USD',
            ShippingContext::FIELD_SUBTOTAL => new Price(),
            ShippingContext::FIELD_CUSTOMER => new Customer(),
        ]);

        $repository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects(self::any())->method('findBy')->willReturn($allProductsShippingOptions);

        $manager = $this->getMockBuilder(ObjectManager::class)->disableOriginalConstructor()->getMock();
        $manager->expects(self::any())->method('getRepository')->willReturn($repository);

        $request = $this->priceRequestFactory->create(
            $this->transport,
            $context,
            [
                PriceRequestFactory::REQUEST_OPTION_FIELD => 'Rate'
            ],
            $this->shippingService
        );

        static::assertEquals($expectedRequest, $request);
    }

    /**
     * @return array
     */
    public function packagesDataProvider()
    {
        return [
            'OnePackage-LBS' => [
                'lineItemCnt' => 2,
                'productWeight' => 30,
                'unitOfWeight' => UPSSettings::UNIT_OF_WEIGHT_LBS,
                'expectedRequest' => $this->createRequest([
                    $this->createPackage(60, UPSSettings::UNIT_OF_WEIGHT_LBS)
                ])
            ],
            'TwoPackages-LBS' => [
                'lineItemCnt' => 3,
                'productWeight' => 50,
                'unitOfWeight' => UPSSettings::UNIT_OF_WEIGHT_LBS,
                'expectedRequest' => $this->createRequest([
                    $this->createPackage(150, UPSSettings::UNIT_OF_WEIGHT_LBS),
                ])
            ],
            'OnePackage-KGS' => [
                'lineItemCnt' => 2,
                'productWeight' => 30,
                'unitOfWeight' => UPSSettings::UNIT_OF_WEIGHT_KGS,
                'expectedRequest' => $this->createRequest([
                    $this->createPackage(60, UPSSettings::UNIT_OF_WEIGHT_KGS)
                ])
            ],
            'TwoPackages-KGS' => [
                'lineItemCnt' => 3,
                'productWeight' => 30,
                'unitOfWeight' => UPSSettings::UNIT_OF_WEIGHT_KGS,
                'expectedRequest' => $this->createRequest([
                    $this->createPackage(60, UPSSettings::UNIT_OF_WEIGHT_KGS),
                    $this->createPackage(30, UPSSettings::UNIT_OF_WEIGHT_KGS),
                ])
            ],
            'NoPackages' => [
                'lineItemCnt' => 0,
                'productWeight' => 30,
                'unitOfWeight' => UPSSettings::UNIT_OF_WEIGHT_KGS,
                'expectedRequest' => null
            ],
        ];
    }

    public function testCreateWithNullShippingAddress()
    {
        $priceRequest = $this->priceRequestFactory->create($this->transport, new ShippingContext([]), []);

        self::assertNull($priceRequest);
    }

    /**
     * @param int $weight
     * @param string $unitOfWeight
     * @return Package
     */
    protected function createPackage($weight, $unitOfWeight)
    {
        $expectedPackage = new Package();
        $expectedPackage
            ->setPackagingTypeCode('00')
            ->setWeight((string)$weight)
            ->setWeightCode($unitOfWeight);

        return $expectedPackage;
    }

    /**
     * @param array $expectedPackages
     * @return PriceRequest
     */
    protected function createRequest($expectedPackages)
    {
        $expectedRequest = new PriceRequest();
        $expectedRequest
            ->setSecurity('some user', 'some password', 'some key')
            ->setRequestOption('Rate')
            ->setShipper('some name', 'some number', new AddressStub())
            ->setShipFrom('some name', new AddressStub())
            ->setShipTo(null, new AddressStub())
            ->setPackages($expectedPackages);
        
        return $expectedRequest;
    }
}
