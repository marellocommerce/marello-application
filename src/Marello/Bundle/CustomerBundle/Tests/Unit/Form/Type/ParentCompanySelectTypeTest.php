<?php

namespace Marello\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Form\Type\ParentCompanySelectType;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\FormView;
use PHPUnit\Framework\TestCase;

class ParentCompanySelectTypeTest extends TestCase
{
    /**
     * @var ParentCompanySelectType
     */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new ParentCompanySelectType();
    }

    public function testGetParent()
    {
        $this->assertEquals(OroJquerySelect2HiddenType::class, $this->type->getParent());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(
                function (array $options) {
                    $this->assertArrayHasKey('autocomplete_alias', $options);
                    $this->assertArrayHasKey('configs', $options);
                    $this->assertEquals('marello_company_parent', $options['autocomplete_alias']);
                    $this->assertEquals(
                        [
                            'component' => 'autocomplete-entity-parent',
                            'placeholder' => 'marello.customer.company.form.choose_parent'
                        ],
                        $options['configs']
                    );
                }
            );

        $this->type->configureOptions($resolver);
    }

    /**
     * @param object|null $parentData
     * @param int|null $expectedParentId
     * @dataProvider buildViewDataProvider
     */
    public function testBuildView($parentData, $expectedParentId)
    {
        $parentForm = $this->createMock('Symfony\Component\Form\FormInterface');
        $parentForm->expects($this->any())
            ->method('getData')
            ->willReturn($parentData);

        $formView = new FormView();

        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('getParent')
            ->willReturn($parentForm);

        $this->type->buildView($formView, $form, []);

        $this->assertArrayHasKey('configs', $formView->vars);
        $this->assertArrayHasKey('entityId', $formView->vars['configs']);
        $this->assertEquals($expectedParentId, $formView->vars['configs']['entityId']);
    }

    /**
     * @return array
     */
    public function buildViewDataProvider()
    {
        $customerId = 42;
        $customer = new Company();

        $reflection = new \ReflectionProperty(get_class($customer), 'id');
        $reflection->setValue($customer, $customerId);

        return [
            'without customer' => [
                'parentData' => null,
                'expectedParentId' => null,
            ],
            'with customer' => [
                'parentData' => $customer,
                'expectedParentId' => $customerId,
            ],
        ];
    }
}
