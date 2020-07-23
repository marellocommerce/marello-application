<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Marello\Bundle\Magento2Bundle\Model\ExtendProduct;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\CreatedAtAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\CreatedAtAwareTrait;
use Oro\Bundle\EntityBundle\EntityProperty\UpdatedAtAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\UpdatedAtAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Marello\Bundle\ProductBundle\Entity\Product as LinkedProduct;

/**
 * Keeps syncing states of product with remote Magento 2 instance by single channel
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\Magento2Bundle\Entity\Repository\ProductRepository")
 * @ORM\Table(
 *  name="marello_m2_product",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unq_product_channel_idx", columns={"product_id", "channel_id"})
 *  }
 * )
 * @Config()
 */
class Product extends ExtendProduct implements
    OriginAwareInterface,
    IntegrationAwareInterface,
    CreatedAtAwareInterface,
    UpdatedAtAwareInterface
{
    use IntegrationEntityTrait, NullableOriginTrait, CreatedAtAwareTrait, UpdatedAtAwareTrait;

    const STATUS_CODE = 'marello_m2_p_status';

    public const STATUS_READY = 'ready';
    public const STATUS_SYNC_ISSUE = 'syncIssue';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", nullable=false)
     */
    protected $sku;

    /**
     * @todo Think about renaming field on innerProduct, wrappedProduct and etc.
     * @todo Update repository after that
     *
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $product;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->product->getId();
    }

    /**
     * @param LinkedProduct $product
     * @return $this
     */
    public function setProduct(LinkedProduct $product): self
    {
        $this->product = $product;

        return $this;
    }
}
