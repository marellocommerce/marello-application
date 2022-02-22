<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Marello\Bundle\PaymentTermBundle\Form\Type\PaymentTermSelectType;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentTermSelectTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /**
     * @inheritDoc
     */
    public function getTypes()
    {
        return [
            PaymentTermSelectType::class => $this->getPaymentTermSelectType(),
        ];
    }

    public function testConfigureOption()
    {
        $resolver = new OptionsResolver();

        $formType = $this->getPaymentTermSelectType(2);
        $formType->configureOptions($resolver);
        $options = $resolver->resolve();
        $callback = $options['choice_label'];

        self::assertEquals(PaymentTerm::class, $options['class']);

        self::assertTrue(is_callable($callback));

        foreach ($this->getPaymentTermEntities() as $label => $paymentTerm) {
            $localizedValue = $callback($paymentTerm);

            self::assertInstanceOf(LocalizedFallbackValue::class, $localizedValue);
            self::assertEquals($label, (string)$localizedValue);
        }
    }

    public function testGetParent()
    {
        static::assertEquals(EntityType::class, $this->getPaymentTermSelectType()->getParent());
    }

    public function testGetBlockPrefix()
    {
        static::assertEquals(PaymentTermSelectType::BLOCK_PREFIX, $this->getPaymentTermSelectType()->getBlockPrefix());
    }

    protected function getPaymentTermEntities()
    {
        return [
            'term 1' => $this->getEntity(PaymentTerm::class, [
                'id' => 1,
                'code' => 'term1',
                'term' => 14,
                'labels' => [
                    (new LocalizedFallbackValue())->setString('term 1'),
                ],
            ]),
            'term 2' => $this->getEntity(PaymentTerm::class, [
                'id' => 2,
                'code' => 'term2',
                'term' => 30,
                'labels' => [
                    (new LocalizedFallbackValue())->setString('term 2'),
                ],
            ]),
        ];
    }

    protected function getPaymentTermSelectType($numberOfCalls = 0)
    {
        /** @var LocalizationHelper|\PHPUnit\Framework\MockObject\MockObject */
        $localizationHelper = $this->createMock(LocalizationHelper::class);
        $localizationHelper->expects(self::exactly($numberOfCalls))
            ->method('getLocalizedValue')
            ->willReturnCallback(
                function (Collection $localizedValues) {
                    return $localizedValues->first();
                }
            );

        return new PaymentTermSelectType($localizationHelper);
    }
}
