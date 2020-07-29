<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Status;

/**
 * @ORM\Entity
 * @ORM\Table(
 *  name="marello_m2_webs_integr_status",
 *  uniqueConstraints={
 *    @ORM\UniqueConstraint(name="unq_integration_status", columns={"status_id"})
 *  }
 * )
 */
class WebsiteIntegrationStatus
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Website
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\Magento2Bundle\Entity\Website")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $website;

    /**
     * @todo Rename this to innerStatus
     * @todo Update repository after that
     *
     * @var Status
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Status", cascade={"persist"})
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $status;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Website|null
     */
    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    /**
     * @param Website|null $website
     * @return $this
     */
    public function setWebsite(Website $website = null): self
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return Status
     */
    public function getInnerStatus(): ?Status
    {
        return $this->status;
    }

    /**
     * @param Status|null $status
     * @return $this
     */
    public function setInnerStatus(Status $status = null): self
    {
        $this->status = $status;
        return $this;
    }
}
