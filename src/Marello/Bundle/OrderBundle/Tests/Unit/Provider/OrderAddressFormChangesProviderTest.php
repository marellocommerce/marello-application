<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\OrderBundle\Provider\OrderAddressFormChangesProvider;
use Twig\Environment;

class OrderAddressFormChangesProviderTest extends TestCase
{
    /**
     * @var Environment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $twigEngine;

    /**
     * @var FormFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $formFactory;

    /**
     * @var |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressType = 'billing';

    /**
     * @var OrderAddressFormChangesProvider
     */
    protected $orderAddressFormChangesProvider;

    protected function setUp(): void
    {
        $this->twigEngine = $this->createMock(Environment::class);
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
        $type->expects($this->once())->method('getInnerType')->willReturn(new AddressType());

        $formConfig = $this->createMock('Symfony\Component\Form\FormConfigInterface');
        $formConfig->expects($this->once())->method('getType')->willReturn($type);
        $formConfig->expects($this->once())->method('getOptions')->willReturn([]);

        /** @var Form|\PHPUnit\Framework\MockObject\MockObject $oldForm */
        $oldForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $oldForm->expects($this->any())->method('getName')->willReturn('order');

        $addressField = sprintf('%sAddress', $this->addressType);

        $oldForm->expects($this->once())
            ->method('has')
            ->with($addressField)
            ->willReturnOnConsecutiveCalls(true, false);

        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $field */
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
            ->with('@MarelloOrder/Form/customerAddressSelector.html.twig', ['form' => $fieldView])
            ->willReturn('view1');

        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $field1 */
        $newField = $this->createMock('Symfony\Component\Form\FormInterface');
        $newField->expects($this->once())->method('createView')->willReturn($fieldView);
        /** @var Form|\PHPUnit\Framework\MockObject\MockObject $oldForm */
        $newForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $builder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');
        $builder->expects($this->once())->method('add')->with($addressField, AddressType::class, $this->isType('array'))
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
