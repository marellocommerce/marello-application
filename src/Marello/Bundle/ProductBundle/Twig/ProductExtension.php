<?php

namespace Marello\Bundle\ProductBundle\Twig;

use Marello\Bundle\CatalogBundle\Provider\CategoriesIdsProvider;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductExtension extends AbstractExtension
{
    const NAME = 'marello_product';
    
    /** @var ChannelProvider $channelProvider */
    protected $channelProvider;

    /** @var CategoriesIdsProvider $categoriesIdsProvider */
    protected $categoriesIdsProvider;

    /** @var DoctrineHelper $doctrineHelper */
    private $doctrineHelper;

    /** @var TokenAccessorInterface $tokenAccessor */
    private $tokenAccessor;

    /**
     * @param ChannelProvider $channelProvider
     * @param CategoriesIdsProvider $categoriesIdsProvider
     */
    public function __construct(ChannelProvider $channelProvider, CategoriesIdsProvider $categoriesIdsProvider)
    {
        $this->channelProvider = $channelProvider;
        $this->categoriesIdsProvider = $categoriesIdsProvider;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_sales_get_saleschannel_ids',
                [$this, 'getSalesChannelsIds']
            ),
            new TwigFunction(
                'marello_product_get_categories_ids',
                [$this, 'getCategoriesIds']
            ),
            new TwigFunction(
                'marello_get_product_by_sku',
                [$this, 'getProductBySku']
            )
        ];
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function getSalesChannelsIds(Product $product)
    {
        return $this->channelProvider->getSalesChannelsIds($product);
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function getCategoriesIds(Product $product)
    {
        return $this->categoriesIdsProvider->getCategoriesIds($product);
    }

    /**
     * @param $sku
     * @return \Extend\Entity\EX_MarelloProductBundle_Product|Product|null
     */
    public function getProductBySku($sku)
    {
        if (!$this->doctrineHelper || !$sku) {
            return null;
        }

        $organization = null;
        if ($this->tokenAccessor) {
            $organization = $this->tokenAccessor->getOrganization();
        }

        /** @var ProductRepository $productRepository */
        $productRepository = $this->doctrineHelper->getEntityRepository(Product::class);
        return $productRepository->findOneBySku($sku, $organization);
    }

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setOroEntityDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function setTokenAccessor(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }
}
