<?php

namespace Marello\Bundle\OrderBundle\Twig\Sandbox;

use Proxies\__CG__\Extend\Entity\EV_Marello_Product_Unit;
use Oro\Bundle\EntityBundle\Twig\Sandbox\EntityVariablesProviderInterface;

class OrderEntitiesVariablesProvider implements EntityVariablesProviderInterface
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