<?php

namespace Marello\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Marello\Bundle\CustomerBundle\Form\Type\CompanySelectType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanySelectTypeTest extends TestCase
{
    /**
     * @var CompanySelectType
     */
    protected $type;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->type = new CompanySelectType();
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->type->getParent());
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|MockObject $resolver */
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'))
            ->willReturnCallback(
                function (array $options) {
                    $this->assertArrayHasKey('autocomplete_alias', $options);
                    $this->assertArrayHasKey('create_form_route', $options);
                    $this->assertArrayHasKey('configs', $options);
                    $this->assertEquals('marello_customer_company', $options['autocomplete_alias']);
                    $this->assertEquals('marello_customer_company_create', $options['create_form_route']);
                    $this->assertEquals(['placeholder' => 'marello.customer.company.form.choose'], $options['configs']);
                }
            );

        $this->type->configureOptions($resolver);
    }
}
