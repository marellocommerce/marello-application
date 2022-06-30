<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Entity\Repository\CompanyRepository;
use Marello\Bundle\CustomerBundle\Form\Type\CompanySelectType;
use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\OrderBundle\Provider\OrderCompanyCustomerFormChangesProvider;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

use PHPUnit\Framework\TestCase;

class OrderCompanyCustomerFormChangesProviderTest extends TestCase
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
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var OrderCompanyCustomerFormChangesProvider
     */
    protected $provider;

    protected function setUp(): void
    {
        $this->twigEngine = $this->createMock(Environment::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->provider = new OrderCompanyCustomerFormChangesProvider(
            $this->twigEngine,
            $this->formFactory,
            $this->registry
        );
    }

    public function testProcessFormChangesWithoutCustomerData()
    {
        $oldForm = $this->createMock(Form::class);
        $oldForm->expects($this->once())
            ->method('has')
            ->with('company')
            ->willReturn(true);

        $context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $oldForm,
            FormChangeContext::SUBMITTED_DATA_FIELD => [],
            FormChangeContext::RESULT_FIELD => []
        ]);

        $this->registry->expects($this->never())
            ->method('getRepository')
            ->with(Company::class);

        $this->provider->processFormChanges($context);
        $this->assertEquals([], $context->getResult());
    }

    public function testProcessFormChangesWithoutCompanyId()
    {
        $type = $this->createMock(ResolvedFormTypeInterface::class);
        $type->expects($this->once())->method('getInnerType')->willReturn(new CompanySelectType());

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects($this->once())->method('getType')->willReturn($type);
        $formConfig->expects($this->once())->method('getOptions')->willReturn([]);

        $oldForm = $this->createMock(Form::class);
        $oldForm->expects($this->any())->method('getName')->willReturn('order');
        $oldForm->expects($this->once())
            ->method('has')
            ->with('company')
            ->willReturn(true);
        $field = $this->createMock(FormInterface::class);
        $oldForm->expects($this->once())->method('get')->with('company')->willReturn($field);

        $field->expects($this->any())->method('getConfig')->willReturn($formConfig);
        $field->expects($this->any())->method('getName')->willReturn('name');
        $field->expects($this->any())->method('getData')->willReturn([]);

        $fieldView = $this->createMock(FormView::class);
        $this->twigEngine->expects($this->once())
            ->method('render')
            ->with('@MarelloOrder/Form/companySelector.html.twig', ['form' => $fieldView])
            ->willReturn('view1');

        $newField = $this->createMock(FormInterface::class);
        $newField->expects($this->once())->method('createView')->willReturn($fieldView);
        $newForm = $this->createMock(Form::class);
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('add')
            ->with('company', CompanySelectType::class, $this->isType('array'))
            ->willReturnSelf();
        $builder->expects($this->once())->method('getForm')->willReturn($newForm);
        $this->formFactory->expects($this->once())->method('createNamedBuilder')->willReturn($builder);
        $newForm->expects($this->once())->method('get')->with('company')->willReturn($newField);
        $newForm->expects($this->once())->method('submit')->with($this->isType('array'));

        $context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $oldForm,
            FormChangeContext::SUBMITTED_DATA_FIELD => ['customer' => '2'],
            FormChangeContext::RESULT_FIELD => []
        ]);

        $repository = $this->createMock(CompanyRepository::class);
        $repository->expects($this->once())
            ->method('getCompanyIdByCustomerId')
            ->with(2)
            ->willReturn(null);
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with(Company::class)
            ->willReturn($repository);

        $this->provider->processFormChanges($context);
        $this->assertEquals(['company' => 'view1'], $context->getResult());
    }

    public function testProcessFormChanges()
    {
        $type = $this->createMock(ResolvedFormTypeInterface::class);
        $type->expects($this->once())->method('getInnerType')->willReturn(new CompanySelectType());

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig->expects($this->once())->method('getType')->willReturn($type);
        $formConfig->expects($this->once())->method('getOptions')->willReturn([]);

        $oldForm = $this->createMock(Form::class);
        $oldForm->expects($this->any())->method('getName')->willReturn('order');
        $oldForm->expects($this->once())
            ->method('has')
            ->with('company')
            ->willReturn(true);

        $field = $this->createMock(FormInterface::class);
        $oldForm->expects($this->once())->method('get')->with('company')->willReturn($field);

        $field->expects($this->any())->method('getConfig')->willReturn($formConfig);
        $field->expects($this->any())->method('getName')->willReturn('name');
        $field->expects($this->any())->method('getData')->willReturn([]);

        $fieldView = $this->createMock(FormView::class);
        $this->twigEngine->expects($this->once())
            ->method('render')
            ->with('@MarelloOrder/Form/companySelector.html.twig', ['form' => $fieldView])
            ->willReturn('view1');

        $newField = $this->createMock(FormInterface::class);
        $newField->expects($this->once())->method('createView')->willReturn($fieldView);
        $newForm = $this->createMock(Form::class);
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('add')
            ->with('company', CompanySelectType::class, $this->isType('array'))
            ->willReturnSelf();
        $builder->expects($this->once())->method('getForm')->willReturn($newForm);
        $this->formFactory->expects($this->once())->method('createNamedBuilder')->willReturn($builder);
        $newForm->expects($this->once())->method('get')->with('company')->willReturn($newField);
        $newForm->expects($this->once())->method('submit')->with($this->isType('array'));

        $context = new FormChangeContext([
            FormChangeContext::FORM_FIELD => $oldForm,
            FormChangeContext::SUBMITTED_DATA_FIELD => ['customer' => '2'],
            FormChangeContext::RESULT_FIELD => []
        ]);

        $repository = $this->createMock(CompanyRepository::class);
        $repository->expects($this->once())
            ->method('getCompanyIdByCustomerId')
            ->with(2)
            ->willReturn(42);
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with(Company::class)
            ->willReturn($repository);

        $this->provider->processFormChanges($context);
        $this->assertEquals(['company' => 'view1'], $context->getResult());
    }
}
