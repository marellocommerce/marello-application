<?php

namespace Marello\Bundle\ProductBundle\Twig\Provider;

use Extend\Entity\EV_Marello_Product_Unit;
use Proxies\__CG__\Extend\Entity\EV_Marello_Product_Unit as PROX_EV_Marello_Product_Unit;
use Oro\Bundle\EntityBundle\Twig\Sandbox\EntityVariablesProviderInterface;

class ProductUnitVariablesProvider implements EntityVariablesProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getVariableDefinitions(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getVariableGetters(): array
    {
        return [
            EV_Marello_Product_Unit::class => [
                'name' => 'getName',
                'id' => 'getId'
            ],
            PROX_EV_Marello_Product_Unit::class => [
                'name' => 'getName',
                'id' => 'getId'
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getVariableProcessors(string $entityClass): array
    {
        return [];
    }
}
