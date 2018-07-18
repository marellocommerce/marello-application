<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2Type;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Form\Type\UPSTransportSettingsType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizationCollectionType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedPropertyType;
use Oro\Bundle\LocaleBundle\Tests\Unit\Form\Type\Stub\LocalizationCollectionTypeStub;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType as EntityTypeStub;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validation;

class UPSTransportSettingsTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    const DATA_CLASS = 'Marello\Bundle\UPSBundle\Entity\UPSSettings';

    /**
     * @var TransportInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transport;

    /**
     * @var UPSTransportSettingsType
     */
    protected $formType;

    /**
     * @var SymmetricCrypterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $crypter;

    protected function setUp()
    {
        $this->transport = $this->createMock(TransportInterface::class);
        $this->transport->expects(static::any())
            ->method('getSettingsEntityFQCN')
            ->willReturn(static::DATA_CLASS);

        $this->crypter = $this->createMock(SymmetricCrypterInterface::class);

        $this->formType = new UPSTransportSettingsType(
            $this->transport
        );

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|TranslatableEntityType $registry */
        $translatableEntity = $this->getMockBuilder('Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType')
            ->setMethods(['setDefaultOptions', 'buildForm'])
            ->disableOriginalConstructor()
            ->getMock();

        $country = new Country('US');
        $choices = [
            'OroAddressBundle:Country' => ['US' => $country],
        ];

        $translatableEntity->expects(static::any())->method('setDefaultOptions')->will(
            static::returnCallback(
                function (OptionsResolver $resolver) use ($choices) {
                    $choiceList = function (Options $options) use ($choices) {
                        $className = $options->offsetGet('class');
                        if (array_key_exists($className, $choices)) {
                            return new ArrayChoiceList(
                                $choices[$className],
                                function ($item) {
                                    if ($item instanceof Country) {
                                        return $item->getIso2Code();
                                    }

                                    return $item.uniqid('form', true);
                                }
                            );
                        }

                        return new ArrayChoiceList([]);
                    };

                    $resolver->setDefault('choice_list', $choiceList);
                }
            )
        );
        $entityType = new EntityTypeStub(
            [
                1 => $this->getEntity(
                    'Marello\Bundle\UPSBundle\Entity\ShippingService',
                    [
                        'id' => 1,
                        'code' => '01',
                        'description' => 'UPS Next Day Air',
                        'country' => $country
                    ]
                ),
                2 => $this->getEntity(
                    'Marello\Bundle\UPSBundle\Entity\ShippingService',
                    [
                        'id' => 2,
                        'code' => '03',
                        'description' => 'UPS Ground',
                        'country' => $country
                    ]
                ),
            ],
            'entity'
        );

        /** @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $localizedFallbackValue = new LocalizedFallbackValueCollectionType($registry);

        return [
            new PreloadedExtension(
                [
                    'entity' => $entityType,
                    'genemu_jqueryselect2_translatable_entity' => new Select2Type('translatable_entity'),
                    'translatable_entity' => $translatableEntity,
                    LocalizedPropertyType::class => new LocalizedPropertyType(),
                    LocalizationCollectionType::class => new LocalizationCollectionTypeStub(),
                    LocalizedFallbackValueCollectionType::class => $localizedFallbackValue,
                    new OroEncodedPlaceholderPasswordType($this->crypter),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @param UPSSettings $defaultData
     * @param array|UPSSettings $submittedData
     * @param bool $isValid
     * @param UPSSettings $expectedData
     * @dataProvider submitProvider
     */
    public function testSubmit(
        UPSSettings $defaultData,
        array $submittedData,
        $isValid,
        UPSSettings $expectedData
    ) {
        if (count($submittedData) > 0) {
            $this->crypter
                ->expects($this->once())
                ->method('encryptData')
                ->with($submittedData['upsApiPassword'])
                ->willReturn($submittedData['upsApiPassword']);
        }
        $form = $this->factory->create($this->formType, $defaultData, []);

        static::assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);

        static::assertEquals($isValid, $form->isValid());
        static::assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
    {
        /** @var ShippingService $expectedShippingService */
        $expectedShippingService = $this->getEntity(
            'Marello\Bundle\UPSBundle\Entity\ShippingService',
            [
                'id' => 1,
                'code' => '01',
                'description' => 'UPS Next Day Air',
                'country' => new Country('US')
            ]
        );
        return [
            'service without value' => [
                'defaultData' => new UPSSettings(),
                'submittedData' => [],
                'isValid' => false,
                'expectedData' => (new UPSSettings())
                    ->addLabel(new LocalizedFallbackValue())
            ],
            'service with value' => [
                'defaultData' => new UPSSettings(),
                'submittedData' => [
                    'labels' => [
                        'values' => [ 'default' => 'first label'],
                    ],
                    'upsTestMode' => true,
                    'upsApiUser' => 'user',
                    'upsApiPassword' => 'password',
                    'upsApiKey' => 'key',
                    'upsShippingAccountName' => 'name',
                    'upsShippingAccountNumber' => 'number',
                    'upsPickupType' => '01',
                    'upsUnitOfWeight' => 'KGS',
                    'upsCountry' => 'US',
                    'applicableShippingServices' => [1]
                ],
                'isValid' => true,
                'expectedData' => (new UPSSettings())
                    ->setUpsTestMode(true)
                    ->setUpsApiUser('user')
                    ->setUpsApiPassword('password')
                    ->setUpsApiKey('key')
                    ->setUpsShippingAccountName('name')
                    ->setUpsShippingAccountNumber('number')
                    ->setUpsPickupType('01')
                    ->setUpsUnitOfWeight('KGS')
                    ->setUpsCountry(new Country('US'))
                    ->addApplicableShippingService($expectedShippingService)
                    ->addLabel((new LocalizedFallbackValue())->setString('first label'))
            ]
        ];
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|\PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects(static::once())
            ->method('setDefaults')
            ->with([
                'data_class' => $this->transport->getSettingsEntityFQCN()
            ]);

        $this->formType->configureOptions($resolver);
    }

    public function testGetBlockPrefix()
    {
        static::assertEquals(UPSTransportSettingsType::BLOCK_PREFIX, $this->formType->getBlockPrefix());
    }
}
