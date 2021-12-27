<?php

namespace Marello\Bundle\PaymentTermBundle\Tests\Unit\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Marello\Bundle\PaymentTermBundle\Form\Type\PaymentTermType;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizationCollectionType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Oro\Bundle\LocaleBundle\Tests\Unit\Form\Type\Stub\LocalizationCollectionTypeStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

class PaymentTermTypeTest extends FormIntegrationTestCase
{
    protected function getExtensions()
    {
        /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $localizedFallbackValue = new LocalizedFallbackValueCollectionType($registry);

        return [
            new PreloadedExtension(
                [
                    LocalizationCollectionType::class => new LocalizationCollectionTypeStub(),
                    LocalizedFallbackValueCollectionType::class => $localizedFallbackValue,
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @param PaymentTerm $defaultData
     * @param array|PaymentTerm $submittedData
     * @param bool $isValid
     * @param PaymentTerm $expectedData
     * @dataProvider submitProvider
     */
    public function testSubmit(
        PaymentTerm $defaultData,
        array $submittedData,
        $isValid,
        PaymentTerm $expectedData
    ) {
        $form = $this->factory->create(PaymentTermType::class, $defaultData, []);

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
            'empty' => [
                'defaultData' => new PaymentTerm(),
                'submittedData' => [],
                'isValid' => false,
                'expectedData' => (new PaymentTerm())->addLabel(new LocalizedFallbackValue()),
            ],
            'not empty' => [
                'defaultData' => new PaymentTerm(),
                'submittedData' => [
                    'code' => 'test14',
                    'term' => '14',
                    'labels' => [
                        'values' => ['default' => 'first label'],
                    ],
                ],
                'isValid' => true,
                'expectedData' => (new PaymentTerm())
                    ->setCode('test14')
                    ->setTerm(14)
                    ->addlabel((new LocalizedFallbackValue())->setString('first label'))
            ],
        ];
    }

    public function testGetBlockType()
    {
        $formType = new PaymentTermType();

        static::assertEquals(PaymentTermType::BLOCK_PREFIX, $formType->getBlockPrefix());
    }
}
