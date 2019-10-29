<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ServicePointBundle\Model\ExtendServicePoint;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

/**
 * @ORM\Entity(repositoryClass="Marello\Bundle\ServicePointBundle\Entity\Repository\ServicePointRepository")
 * @ORM\Table(name="marello_sp_servicepoint")
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *     routeName="marello_servicepoint_servicepoint_index",
 *     routeView="marello_servicepoint_servicepoint_view",
 *     routeCreate="marello_servicepoint_servicepoint_create",
 *     defaultValues={
 *         "entity"={
 *             "label"="marello.servicepoint.entity_label",
 *             "plural_label"="marello.servicepoint.entity_plural_label"
 *         },
 *         "dataaudit"={
 *              "auditable"=true
 *         },
 *         "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *         }
 *     }
 * )
 */
class ServicePoint extends ExtendServicePoint implements DatesAwareInterface
{
    use DatesAwareTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.id.label"
     *     }
     * })
     */
    protected $id;

    /**
     * @var Collection|LocalizedFallbackValue[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="marello_sp_servicepoint_labels",
     *      joinColumns={
     *          @ORM\JoinColumn(name="service_point_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.labels.label"
     *     }
     * })
     */
    protected $labels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="marello_sp_servicepoint_descrs",
     *      joinColumns={
     *          @ORM\JoinColumn(name="service_point_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.descriptions.label"
     *     }
     * })
     */
    protected $descriptions;

    /**
     * @var ServicePointAddress
     *
     * @ORM\OneToOne(targetEntity="Marello\Bundle\ServicePointBundle\Entity\ServicePointAddress", cascade={"ALL"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.address.label"
     *     }
     * })
     */
    protected $address;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="decimal", precision=10, scale=7)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.latitude.label"
     *     }
     * })
     */
    protected $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="decimal", precision=10, scale=7)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.longitude.label"
     *     }
     * })
     */
    protected $longitude;

    /**
     * @var File
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\AttachmentBundle\Entity\File", cascade={"ALL"})
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=true)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.image.label"
     *     }
     * })
     */
    protected $image;

    /**
     * @var Collection|ServicePointFacility[]
     *
     * @ORM\OneToMany(targetEntity="Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility", mappedBy="servicePoint")
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.service_point_facilities.label"
     *     }
     * })
     */
    protected $servicePointFacilities;

    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->descriptions = new ArrayCollection();
        $this->servicePointFacilities = new ArrayCollection();

        parent::__construct();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getLabels(): Collection
    {
        return $this->labels;
    }

    /**
     * @param Collection|LocalizedFallbackValue[] $labels
     * @return ServicePoint
     */
    public function setLabels(Collection $labels): ServicePoint
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $label
     * @return ServicePoint
     */
    public function addLabel(LocalizedFallbackValue $label): ServicePoint
    {
        if (!$this->labels->contains($label)) {
            $this->labels[] = $label;
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $label
     * @return ServicePoint
     */
    public function removeLabel(LocalizedFallbackValue $label): ServicePoint
    {
        $this->labels->removeElement($label);

        return $this;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getDescriptions(): Collection
    {
        return $this->descriptions;
    }

    /**
     * @param Collection|LocalizedFallbackValue[] $descriptions
     * @return ServicePoint
     */
    public function setDescriptions(Collection $descriptions): ServicePoint
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $description
     * @return ServicePoint
     */
    public function addDescription(LocalizedFallbackValue $description): ServicePoint
    {
        if (!$this->descriptions->contains($description)) {
            $this->descriptions[] = $description;
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $description
     * @return ServicePoint
     */
    public function removeDescription(LocalizedFallbackValue $description): ServicePoint
    {
        $this->descriptions->removeElement($description);

        return $this;
    }

    /**
     * @return ServicePointAddress
     */
    public function getAddress(): ?ServicePointAddress
    {
        return $this->address;
    }

    /**
     * @param ServicePointAddress $address
     * @return ServicePoint
     */
    public function setAddress(ServicePointAddress $address): ServicePoint
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return ?float
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     * @return ServicePoint
     */
    public function setLatitude(float $latitude): ServicePoint
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return ?float
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     * @return ServicePoint
     */
    public function setLongitude(float $longitude): ServicePoint
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return ?File
     */
    public function getImage(): ?File
    {
        return $this->image;
    }

    /**
     * @param File $image
     * @return ServicePoint
     */
    public function setImage(File $image): ServicePoint
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|ServicePointFacility[]
     */
    public function getServicePointFacilities(): Collection
    {
        return $this->servicePointFacilities;
    }

    /**
     * @param ServicePointFacility $facility
     * @return ServicePoint
     */
    public function addServicePointFacility(ServicePointFacility $facility): ServicePoint
    {
        if (!$this->servicePointFacilities->contains($facility)) {
            $this->servicePointFacilities[] = $facility;
        }

        return $this;
    }

    /**
     * @param ServicePointFacility $facility
     * @return ServicePoint
     */
    public function removeServicePointFacility(ServicePointFacility $facility): ServicePoint
    {
        $this->servicePointFacilities->removeElement($facility);

        return $this;
    }
}
