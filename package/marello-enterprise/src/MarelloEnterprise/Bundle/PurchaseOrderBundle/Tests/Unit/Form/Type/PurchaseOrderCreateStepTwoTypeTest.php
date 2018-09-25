<?php

namespace MarelloEnterprise\Bundle\PurchaseOrderBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PricingBundle\Form\Type\ProductPriceType;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Form\Type\ProductSupplierSelectType;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderCreateStepTwoType;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderItemCollectionType;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderItemType;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\SupplierBundle\Form\Type\SupplierSelectType;
use MarelloEnterprise\Bundle\PurchaseOrderBundle\Form\Extension\PurchaseOrderWarehouseFormExtension;
use Oro\Bundle\CurrencyBundle\Tests\Unit\Utils\CurrencyNameHelperStub;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Tests\Unit\Form\Stub\EntityIdentifierType;
use Oro\Bundle\FormBundle\Tests\Unit\Form\Type\EntitySelectOrCreateInlineFormExtension;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validation;

class PurchaseOrderCreateStepTwoTypeTest extends FormIntegrationTestCase
{
    /**
     * @param array $submittedData
     * @param mixed $expectedData
     * @param mixed $defaultData
     *
     * @dataProvider submitProvider
     */
    public function testSubmit($submittedData, $expectedData, $defaultData = null)
    {
        $form = $this->factory->createNamed(
            PurchaseOrderCreateStepTwoType::NAME,
            PurchaseOrderCreateStepTwoType::NAME,
            $defaultData
        );
        $form->submit($submittedData);
        $this->assertEquals($expectedData['isValid'], $form->isValid());
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        return [
            'valid' => [
                'submittedData' => [
                    'supplier' => 'supplier_1',
                    'dueDate' => '2018-05-16',
                    'itemsAdditional' => [
                        0 => [
                            'product' => 'product_1',
                            'orderedAmount' => 10,
                            'purchasePrice' => [
                                'currency' => 'USD',
                                'value' => 20.20
                            ]
                        ]
                    ],
                    'warehouse' => 'warehouse_1'
                ],
                'expectedData' => [
                    'isValid' => true
                ]
            ],
            'no_submitted_warehouse' => [
                'submittedData' => [
                    'supplier' => 'supplier_1',
                    'dueDate' => '2018-05-16',
                    'itemsAdditional' => [
                        0 => [
                            'product' => 'product_1',
                            'orderedAmount' => 10,
                            'purchasePrice' => [
                                'currency' => 'USD',
                                'value' => 20.20
                            ]
                        ]
                    ],
                ],
                'expectedData' => [
                    'isValid' => false,
                ]
            ],
            'not_existing_warehouse' => [
                'submittedData' => [
                    'supplier' => 'supplier_1',
                    'dueDate' => '2018-05-16',
                    'itemsAdditional' => [
                        0 => [
                            'product' => 'product_1',
                            'orderedAmount' => 10,
                            'purchasePrice' => [
                                'currency' => 'USD',
                                'value' => 20.20
                            ]
                        ]
                    ],
                    'warehouse' => 0
                ],
                'expectedData' => [
                    'isValid' => false
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();


        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $configManager
            ->expects($this->any())
            ->method('getProvider')
            ->will($this->returnValue($configProvider));

        $config = $this->createMock('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');

        $configProvider
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($config));

        $searchRegistry = $this->getMockBuilder('Oro\Bundle\FormBundle\Autocomplete\SearchRegistry')
            ->disableOriginalConstructor()
            ->setMethods(['hasSearchHandler', 'getSearchHandler'])
            ->getMock();

        $handler = $this->createMock('Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface');
        $handler
            ->expects($this->any())
            ->method('getProperties')
            ->will($this->returnValue([]));

        $searchRegistry
            ->expects($this->any())
            ->method('getSearchHandler')
            ->will($this->returnValue($handler));

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->any())
            ->method('find')
            ->willReturnCallback(
                function ($id) {
                    if ($id === 'product_1') {
                        return new Product();
                    } elseif ($id === 'supplier_1') {
                        return new Supplier();
                    } elseif ($id === 'warehouse_1') {
                        return new Warehouse();
                    } else {
                        return null;
                    }
                }
            );
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);

        $metadata = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->any())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue('id'));
        $entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadata));

        $localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocale', 'getCurrency', 'getCurrencySymbolByCurrency'))
            ->getMock();

        $numberFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NumberFormatter')
            ->disableOriginalConstructor()
            ->setMethods(array('isCurrencySymbolPrepend', 'getAttribute'))
            ->getMock();

        $doctrineHelper = $this->createMock(DoctrineHelper::class);

        return [
            new EntitySelectOrCreateInlineFormExtension(
                $entityManager,
                $searchRegistry,
                $configProvider
            ),
            new PreloadedExtension(
                [
                    OroEntitySelectOrCreateInlineType::class =>
                        new OroEntitySelectOrCreateInlineType(
                            $authorizationChecker,
                            $configManager,
                            $entityManager,
                            $searchRegistry
                        ),
                    SupplierSelectType::class => new SupplierSelectType(),
                    OroDateType::class => new OroDateType(),
                    MultipleEntityType::class => new MultipleEntityType($doctrineHelper, $authorizationChecker),
                    new EntityIdentifierType([]),
                    PurchaseOrderItemCollectionType::class => new PurchaseOrderItemCollectionType(),
                    CollectionType::class => new CollectionType(),
                    PurchaseOrderItemType::class => new PurchaseOrderItemType(),
                    ProductSupplierSelectType::class => new ProductSupplierSelectType(),
                    ProductPriceType::class => new ProductPriceType(),
                    OroMoneyType::class => new OroMoneyType($localeSettings, $numberFormatter),
                    PurchaseOrderCreateStepTwoType::class =>
                        new PurchaseOrderCreateStepTwoType(
                            $this->createMock(Router::class),
                            new CurrencyNameHelperStub()
                        )
                ],
                [
                    PurchaseOrderCreateStepTwoType::NAME => [new PurchaseOrderWarehouseFormExtension()]
                ]
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }
}
