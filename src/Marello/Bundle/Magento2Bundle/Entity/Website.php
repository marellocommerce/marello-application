<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\Magento2Bundle\Model\ExtendWebsite;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteRepository")
 * @ORM\Table(
 *  name="marello_m2_website",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unq_site_idx", columns={"channel_id", "origin_id"})
 *  }
 * )
 * @Config()
 */
class Website extends ExtendWebsite implements OriginAwareInterface, IntegrationAwareInterface
{
    use IntegrationEntityTrait, OriginTrait;

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
     * @ORM\Column(name="code", type="string", length=32, nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var Collection|Store[]
     *
     *  @ORM\OneToMany(
     *     targetEntity="Marello\Bundle\Magento2Bundle\Entity\Store",
     *     mappedBy="website"
     * )
     */
    protected $stores;

    public function __construct()
    {
        $this->stores = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string|null $code
     * @return Website
     */
    public function setCode(string $code = null): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $name
     *
     * @return Website
     */
    public function setName(string $name = null): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Collection|Store[]
     */
    public function getStores()
    {
        return $this->stores;
    }

    /**
     * @param Store $store
     *
     * @return $this
     */
    public function addStore(Store $store)
    {
        $this->stores->add($store->setWebsite($this));

        return $this;
    }

    /**
     * @param Store $store
     *
     * @return $this
     */
    public function removeStore(Store $store)
    {
        $this->stores->removeElement($store->setWebsite(null));

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstActiveStoreCode(): ?string
    {
        foreach ($this->stores as $store) {
            if ($store->isActive()) {
                return $store->getCode();
            }
        }

        return null;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }
}
