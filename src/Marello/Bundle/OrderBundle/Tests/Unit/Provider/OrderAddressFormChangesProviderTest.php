<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\OrderBundle\Provider\OrderAddressFormChangesProvider;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Templating\EngineInterface;

class OrderAddressFormChangesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $twigEngine;

    /**
     * @var FormFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactory;

    /**
     * @var |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressType = 'billing';

    /**
     * @var OrderAddressFormChangesProvider
     */
    protected $orderAddressFormChangesProvider;

    protected function setUp()
    {
        $this->twigEngine = $this->createMock(EngineInterface::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->orderAddressFormChangesProvider = new OrderAddressFormChangesProvider(
            $this->twigEngine,
            $this->formFactory,
            $this->addressType
        );
    }

    public function testProcessFormChanges()
    {
        $type = $this->createMock('Symfony\Component\Form\ResolvedFormTypeInterface');
        $type->expects($this->once())->method('getName')->willReturn('type');

        $formConfig = $this->createMock('Symfony\Component\Form\FormConfigInterface');
        $formConfig->expects($this->once())->method('getType')->willReturn($type);
        $formConfig->expects($this->once())->method('getOptions')->willReturn([]);

        /** @var Form|\PHPUnit_Framework_MockObject_MockObject $oldForm */
        $oldForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $oldForm->expects($this->any())->method('getName')->willReturn('order');

        $addressField = sprintf('%sAddress', $this->addressType);

        $oldForm->expects($this->once())
            ->method('has')
            ->with($addressField)
            ->willReturnOnConsecutiveCalls(true, false);

        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $field */
        $field = $this->createMock('Symfony\Component\Form\FormInterface');

        $oldForm->expects($this->once())->method('get')->with($addressField)->willReturn($field);


        $field->expects($this->any())->method('getConfig')->willReturn($formConfig);
        $field->expects($this->any())->method('getName')->willReturn('name');
        $field->expects($this->any())->method('getData')->willReturn([]);

        $fieldView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $this->twigEngine->expects($this->once())
            ->method('render')
            ->with('MarelloOrderBundle:Form:customerAddressSelector.html.twig', ['form' => $fieldView])
            ->willReturn('view1');

        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $field1 */
        $newField = $this->createMock('Symfony\Component\Form\FormInterface');
        $newField->expects($this->once())->method('createView')->willReturn($fieldView);
        /** @var Form|\PHPUnit_Framework_MockObject_MockObject $oldForm */
        $newForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $builder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->once())->method('add')->with($addressField, 'type', $this->isType('array'))
            ->willReturnSelf();
        $builder->expects($this->once())->method('getForm')->willReturn($newForm);
        $this->formFactory->expects($this->once())->method('createNamedBuilder')->willReturn($builder);
        $newForm->expects($this->once())->method('get')->with($addressField)->willReturn($newField);
        $newForm->expects($this->once())->method('submit')->with($this->isType('array'));

        $context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $oldForm,
            FormChangeContext::SUBMITTED_DATA_FIELD => ['order' => []],
            FormChangeContext::RESULT_FIELD => []
        ]);

        $this->orderAddressFormChangesProvider->processFormChanges($context);

        $this->assertEquals([$addressField => 'view1'], $context->getResult());
    }
}
