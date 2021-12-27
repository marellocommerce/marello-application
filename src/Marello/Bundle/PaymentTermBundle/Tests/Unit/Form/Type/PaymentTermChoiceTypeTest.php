<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Marello\Bundle\PaymentTermBundle\Form\Type\PaymentTermChoiceType;
use Marello\Bundle\PaymentTermBundle\Provider\PaymentTermProvider;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PaymentTermChoiceTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /**
     * @inheritDoc
     */
    public function getTypes()
    {
        return [
            PaymentTermChoiceType::class => $this->getPaymentTermChoiceType(),
        ];
    }

    /**
     * @param PaymentTerm|null $defaultData
     * @param string $submittedData
     * @param bool $isValid
     * @param PaymentTerm|null $expectedData
     * @dataProvider submitProvider
     */
    public function testSubmit($defaultData, $submittedData, $isValid, $expectedData)
    {
        $form = $this->factory->create(PaymentTermChoiceType::class, $defaultData);

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
        return [
            'empty_with_default' => [
                'defaultData' => 1,
                'submittedData' => null,
                'isValid' => true,
                'expectedData' => null,
            ],
            'invalid_with_default' => [
                'defaultData' => 1,
                'submittedData' => 3,
                'isValid' => false,
                'expectedData' => null,
            ],
            'valid_with_default' => [
                'defaultData' => 1,
                'submittedData' => 2,
                'isValid' => true,
                'expectedData' => 2,
            ],
            'empty_without_default' => [
                'defaultData' => null,
                'submittedData' => null,
                'isValid' => true,
                'expectedData' => null,
            ],
            'invalid_without_default' => [
                'defaultData' => null,
                'submittedData' => 3,
                'isValid' => false,
                'expectedData' => null,
            ],
            'valid_without_default' => [
                'defaultData' => null,
                'submittedData' => 1,
                'isValid' => true,
                'expectedData' => 1,
            ],
        ];
    }

    public function testGetParent()
    {
        $formType = $this->getPaymentTermChoiceType();

        static::assertEquals(ChoiceType::class, $formType->getParent());
    }

    public function testGetBlockPrefix()
    {
        $formType = $this->getPaymentTermChoiceType();

        static::assertEquals(PaymentTermChoiceType::BLOCK_PREFIX, $formType->getBlockPrefix());
    }

    protected function getPaymentTermEntities()
    {
        return [
            $this->getEntity(PaymentTerm::class, [
                'id' => 1,
                'code' => 'term1',
                'term' => 14,
                'labels' => [
                    (new LocalizedFallbackValue())->setString('term 1'),
                ],
            ]),
            $this->getEntity(PaymentTerm::class, [
                'id' => 2,
                'code' => 'term2',
                'term' => 30,
                'labels' => [
                    (new LocalizedFallbackValue())->setString('term 2'),
                ],
            ]),
        ];
    }

    protected function getPaymentTermChoiceType()
    {
        /** @var PaymentTermProvider|\PHPUnit\Framework\MockObject\MockObject */
        $paymentTermProvider = $this->createMock(PaymentTermProvider::class);
        $paymentTermProvider
            ->method('getPaymentTerms')
            ->willReturn($this->getPaymentTermEntities())
        ;

        /** @var LocalizationHelper|\PHPUnit\Framework\MockObject\MockObject */
        $localizationHelper = $this->createMock(LocalizationHelper::class);
        $localizationHelper
            ->method('getLocalizedValue')
            ->willReturnCallback(
                function (Collection $values) {
                    return $values->first();
                }
            );

        return new PaymentTermChoiceType($paymentTermProvider, $localizationHelper);
    }
}
