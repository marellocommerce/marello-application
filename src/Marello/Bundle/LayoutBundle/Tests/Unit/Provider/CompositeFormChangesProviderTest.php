<?php

namespace Marello\Bundle\LayoutBundle\Tests\Unit\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;
use Marello\Bundle\LayoutBundle\Provider\CompositeFormChangesProvider;
use Marello\Bundle\LayoutBundle\Provider\FormChangesProviderInterface;
use Symfony\Component\Form\FormInterface;

class CompositeFormChangesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeFormChangesProvider
     */
    protected $compositeFormChangesProvider;

    /**
     * @var array
     */
    protected $providersData = [];

    /**
     * @var FormChangeContextInterface
     */
    protected $context;

    protected function setUp()
    {
        $this->providersData = [
            ['class' => 'class1', 'type' => 'type1', 'data' => 'data1'],
            ['class' => 'class1', 'type' => 'type2', 'data' => 'data2'],
            ['class' => 'class2', 'type' => 'type3', 'data' => 'data3'],
            ['class' => 'class2', 'type' => 'type4', 'data' => 'data4'],
        ];

        $this->context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $this->createMock(FormInterface::class),
            FormChangeContext::SUBMITTED_DATA_FIELD => [],
            FormChangeContext::RESULT_FIELD => []
        ]);

        $this->compositeFormChangesProvider = new CompositeFormChangesProvider();

        foreach ($this->providersData as $data) {
            $this->compositeFormChangesProvider->addProvider(
                $this->createProviderMock($data),
                $data['class'],
                $data['type']
            );
        }
    }

    /**
     * @dataProvider processFormChangesDataProvider
     *
     * @param string $requiredClass
     * @param array $requiredFields
     * @param array $expectedData
     */
    public function testProcessFormChanges(
        $requiredClass,
        array $requiredFields,
        array $expectedData
    ) {
        $this->compositeFormChangesProvider
            ->setRequiredDataClass($requiredClass)
            ->setRequiredFields($requiredFields);

        $this->compositeFormChangesProvider->processFormChanges($this->context);

        static::assertEquals(
            $expectedData,
            $this->context->getResult()
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage requiredDataClass should be specified
     */
    public function testProcessFormChangesNoRequiredClass()
    {
        $this->compositeFormChangesProvider->processFormChanges($this->context);
    }

    /**
     * @param $data
     * @return FormChangesProviderInterface|\PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    protected function createProviderMock($data)
    {
        $provider = $this->createMock(FormChangesProviderInterface::class);
        $provider
            ->expects(static::any())
            ->method('processFormChanges')
            ->willReturnCallback(function () use ($data) {
                $result = $this->context->getResult();
                $result[$data['type']] = $data['data'];
                $this->context->setResult($result);
            });

        return $provider;
    }

    /**
     * @return array
     */
    public function processFormChangesDataProvider()
    {
        return [
            'allFieldsRequiredClass1' => [
                'requiredClass' => 'class1',
                'requiredFields' => [],
                'expectedData' => [
                    'type1' => 'data1',
                    'type2' => 'data2',
                ],
            ],
            'specificFieldRequiredClass1' => [
                'requiredClass' => 'class1',
                'requiredFields' => ['type1'],
                'expectedData' => [
                    'type1' => 'data1'
                ],
            ],
            'allFieldsRequiredClass2' => [
                'requiredClass' => 'class2',
                'requiredFields' => [],
                'expectedData' => [
                    'type3' => 'data3',
                    'type4' => 'data4',
                ],
            ],
            'specificFieldRequiredClass2' => [
                'requiredClass' => 'class2',
                'requiredFields' => ['type3'],
                'expectedData' => [
                    'type3' => 'data3'
                ],
            ],
            'classAndTypeNotMatched' => [
                'requiredClass' => 'class2',
                'requiredFields' => ['type1'],
                'expectedData' => [],
            ],
        ];
    }
}
