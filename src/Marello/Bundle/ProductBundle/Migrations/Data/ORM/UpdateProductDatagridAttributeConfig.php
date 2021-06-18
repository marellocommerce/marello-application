<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class UpdateProductDatagridAttributeConfig extends AbstractFixture implements ContainerAwareInterface
{
    use MakeProductAttributesTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->updateProductAttributeDataGridOptions(
            [
                'taxCode' => [
                    'is_visible' => 3
                ],
                'warranty' => [
                    'is_visible' => 3
                ]
            ]
        );
        $this->getConfigManager()->flush();
    }
}
