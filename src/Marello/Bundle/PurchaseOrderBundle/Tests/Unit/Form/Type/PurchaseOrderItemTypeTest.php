<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\PricingBundle\Form\Type\ProductPriceType;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Form\Type\ProductSupplierSelectType;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderItemType;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Tests\Unit\Form\Type\EntitySelectOrCreateInlineFormExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class PurchaseOrderItemTypeTest extends FormIntegrationTestCase
{
    /**
     * @var PurchaseOrderItemType
     */
    protected $formType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formType = new PurchaseOrderItemType();
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals(PurchaseOrderItemType::BLOCK_PREFIX, $this->formType->getBlockPrefix());
    }

    /**
     * @param array $submittedData
     * @param mixed $expectedData
     * @param mixed $defaultData
     *
     * @dataProvider submitProvider
     */
    public function testSubmit($submittedData, $expectedData, $defaultData = null)
    {
        $form = $this->factory->create(PurchaseOrderItemType::class, $defaultData);

        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);
        $this->assertEquals($expectedData['isValid'], $form->isValid());
        //$this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        return [
            'valid' => [
                'submittedData' => [
                    'product' => 3,
                    'orderedAmount' => 10,
                    'purchasePrice' => [
                        'value' => 42,
                        'currency' => 'USD'
                    ],
                ],
                'expectedData' => [
                    'isValid' => true
                ]
            ],
            'not_valid_amount' => [
                'submittedData' => [
                    'product' => 3,
                    'orderedAmount' => -10,
                    'purchasePrice' => [
                        'value' => 42,
                        'currency' => 'USD'
                    ],
                ],
                'expectedData' => [
                    'isValid' => false,
                ]
            ],
            'not_valid_price' => [
                'submittedData' => [
                    'product' => 3,
                    'orderedAmount' => 10,
                    'purchasePrice' => [
                        'value' => -42,
                        'currency' => 'USD'
                    ],
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
        $authorizationChecker->method('isGranted')
            ->willReturn(true);
        $featureChecker = $this->createMock(FeatureChecker::class);

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
            ->willReturn(new Product());
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

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnArgument(0);

        return [
            new EntitySelectOrCreateInlineFormExtension(
                $entityManager,
                $searchRegistry,
                $configProvider
            ),
            new PreloadedExtension([
                OroEntitySelectOrCreateInlineType::class => new OroEntitySelectOrCreateInlineType(
                    $authorizationChecker,
                    $featureChecker,
                    $configManager,
                    $entityManager,
                    $searchRegistry
                ),
                ProductSupplierSelectType::class => new ProductSupplierSelectType(),
                ProductPriceType::class => new ProductPriceType($translator),
                OroMoneyType::class => new OroMoneyType($localeSettings, $numberFormatter)
            ], []),
            new ValidatorExtension(Validation::createValidator())
        ];
    }
}
