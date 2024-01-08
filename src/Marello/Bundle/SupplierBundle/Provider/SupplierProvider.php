<?php

namespace Marello\Bundle\SupplierBundle\Provider;

use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\CurrencyBundle\Model\LocaleSettings;

class SupplierProvider
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;
    
    /**
     * SupplierProvider constructor.
     * @param ObjectManager $manager
     * @param LocaleSettings $localeSettings
     */
    public function __construct(ObjectManager $manager, LocaleSettings $localeSettings)
    {
        $this->manager = $manager;
        $this->localeSettings = $localeSettings;
    }

    /**
     * Returns ids of all related suppliers for a product.
     *
     * @param Product $product
     *
     * @return array $ids
     */
    public function getProductSuppliersIds(Product $product)
    {
        $ids = [];
        $product
            ->getSuppliers()
            ->map(function (ProductSupplierRelation $productSupplierRelation) use (&$ids) {
                $ids[] = $productSupplierRelation->getId();
            });

        return $ids;
    }

    /**
     * Get Default data from supplier
     * Default data consists of name, priority and canDropship fields
     * @param $supplierId
     * @return array
     */
    public function getSupplierDefaultDataById($supplierId)
    {
        $entityRepository = $this->manager->getRepository(Supplier::class);
        $supplier = $entityRepository->find($supplierId);

        return [
            'name' => $supplier->getName(),
            'priority' => $supplier->getPriority(),
            'canDropship' => $supplier->getCanDropship(),
            'currency' => $this->localeSettings->getCurrencySymbolByCurrency($supplier->getCurrency())
        ];
    }
}
