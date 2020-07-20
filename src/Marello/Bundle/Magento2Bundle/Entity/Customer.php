<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\CustomerBundle\Entity\Customer as InnerCustomer;
use Marello\Bundle\Magento2Bundle\Model\ExtendCustomer;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * Keeps syncing states of order with remote Magento 2 instance by single channel
 *
 * @ORM\Entity(repositoryClass="Marello\Bundle\Magento2Bundle\Entity\Repository\CustomerRepository")
 * @ORM\Table(
 *  name="marello_m2_customer",
 *  indexes={
 *     @ORM\Index(name="idx_customer_hash_channel_idx", columns={"channel_id", "hash_id"})
 *  },
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unq_customer_channel_idx", columns={"channel_id", "origin_id"})
 *  }
 * )
 * @Config()
 */
class Customer extends ExtendCustomer implements OriginAwareInterface, IntegrationAwareInterface
{
    use IntegrationEntityTrait, NullableOriginTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Represents Marello Customer that was created based on the data got from Magento
     *
     * @var InnerCustomer
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\CustomerBundle\Entity\Customer", cascade={"persist"})
     * @ORM\JoinColumn(name="inner_customer_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true
     *          }
     *      }
     * )
     */
    protected $innerCustomer;

    /**
     * Contains hashed concatenated "identity" fields value of inner customer,
     * it uses to simplify lookup of magento representative guest customer that doesn't have
     * other identifiers except: email, firstName, lastName
     *
     * @var string
     *
     * @ORM\Column(name="hash_id", type="string", length=32, nullable=true)
     */
    protected $hashId;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return InnerCustomer|null
     */
    public function getInnerCustomer(): ?InnerCustomer
    {
        return $this->innerCustomer;
    }

    /**
     * @param InnerCustomer $innerCustomer
     * @return $this
     */
    public function setInnerCustomer(InnerCustomer $innerCustomer): self
    {
        $this->innerCustomer = $innerCustomer;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHashId(): ?string
    {
        return $this->hashId;
    }

    /**
     * @param string $hashId
     * @return $this
     */
    public function setHashId(string $hashId): self
    {
        $this->hashId = $hashId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGuest(): bool
    {
        return null === $this->originId;
    }
}
