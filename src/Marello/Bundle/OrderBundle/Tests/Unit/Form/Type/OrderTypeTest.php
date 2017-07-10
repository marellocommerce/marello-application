<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Form\Type;

use Marello\Bundle\OrderBundle\Form\Type\OrderType;
use Marello\Bundle\SalesBundle\Entity\Repository\SalesChannelRepository;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;

class OrderTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /**
     * @var SalesChannelRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesChannelRepository;

    /**
     * @var OrderType
     */
    protected $type;

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $userSelectType = new StubEntityTyp(
            [
                1 => $this->getEntity('Oro\Bundle\UserBundle\Entity\User', 1),
                2 => $this->getEntity('Oro\Bundle\UserBundle\Entity\User', 2),
            ],
            'oro_user_select'
        );

        $customerSelectType = new StubEntityType(
            [
                1 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', 1),
                2 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\Customer', 2),
            ],
            CustomerSelectType::NAME
        );

        $customerUserSelectType = new StubEntityType(
            [
                1 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerUser', 1),
                2 => $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerUser', 2),
            ],
            CustomerUserSelectType::NAME
        );

        $priceListSelectType = new StubEntityType(
            [
                1 => $this->getEntity('Oro\Bundle\PricingBundle\Entity\PriceList', 1),
                2 => $this->getEntity('Oro\Bundle\PricingBundle\Entity\PriceList', 2),
            ],
            PriceListSelectType::NAME
        );

        $productUnitSelectionType = $this->prepareProductUnitSelectionType();
        $productSelectType = new ProductSelectTypeStub();
        $entityType = $this->prepareProductEntityType();
        $priceType = $this->preparePriceType();

        /** @var ProductUnitLabelFormatter $ProductUnitLabelFormatter */
        $ProductUnitLabelFormatter = $this
            ->getMockBuilder('Oro\Bundle\ProductBundle\Formatter\ProductUnitLabelFormatter')
            ->disableOriginalConstructor()->getMock();

        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()->getMock();

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repository->expects($this->any())->method('findBy')->willReturn([]);
        $managerRegistry->expects($this->any())->method('getRepository')->willReturn($repository);

        $OrderLineItemType = new OrderLineItemType($managerRegistry, $ProductUnitLabelFormatter);
        $OrderLineItemType->setDataClass('Oro\Bundle\OrderBundle\Entity\OrderLineItem');
        $currencySelectionType = new CurrencySelectionTypeStub();

        $this->validator = $this->createMock(
            'Symfony\Component\Validator\Validator\ValidatorInterface'
        );
        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));


        return [
            new PreloadedExtension(
                [
                    CollectionType::NAME => new CollectionType(),
                    OroDateType::NAME => new OroDateType(),
                    $priceType->getName() => $priceType,
                    $entityType->getName() => $entityType,
                    $userSelectType->getName() => $userSelectType,
                    $productSelectType->getName() => $productSelectType,
                    $productUnitSelectionType->getName() => $productUnitSelectionType,
                    $customerSelectType->getName() => $customerSelectType,
                    $currencySelectionType->getName() => $currencySelectionType,
                    $customerUserSelectType->getName() => $customerUserSelectType,
                    $priceListSelectType->getName() => $priceListSelectType,
                    OrderLineItemsCollectionType::NAME => new OrderLineItemsCollectionType(),
                    OrderDiscountItemsCollectionType::NAME => new OrderDiscountItemsCollectionType(),
                    OrderLineItemType::NAME => $OrderLineItemType,
                    OrderDiscountItemType::NAME => new OrderDiscountItemType(),
                    QuantityTypeTrait::$name => $this->getQuantityType(),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }
}
