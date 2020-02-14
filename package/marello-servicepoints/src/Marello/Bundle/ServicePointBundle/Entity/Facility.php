<?php

namespace Marello\Bundle\ServicePointBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\ServicePointBundle\Model\ExtendFacility;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="marello_sp_facility",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"code"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *     routeName="marello_servicepoint_facility_index",
 *     routeView="marello_servicepoint_facility_view",
 *     routeCreate="marello_servicepoint_facility_create",
 *     defaultValues={
 *         "entity"={
 *             "label"="marello.servicepoint.facility.entity_label",
 *             "plural_label"="marello.servicepoint.facility.entity_plural_label"
 *         },
 *         "security"={
 *            "type"="ACL",
 *            "group_name"="",
 *         }
 *     }
 * )
 */
class Facility extends ExtendFacility
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.facility.id.label"
     *     }
     * })
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32)
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.facility.code.label"
     *     }
     * })
     */
    protected $code;

    /**
     * @var Collection|LocalizedFallbackValue[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="marello_sp_facility_labels",
     *      joinColumns={
     *          @ORM\JoinColumn(name="facility_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     * @ConfigField(defaultValues={
     *     "entity"={
     *         "label" = "marello.servicepoint.facility.labels.label"
     *     }
     * })
     */
    protected $labels;

    public function __construct()
    {
        $this->labels = new ArrayCollection();

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
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Facility
     */
    public function setCode(string $code): Facility
    {
        $this->code = $code;

        return $this;
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
     * @return Facility
     */
    public function setLabels(Collection $labels): Facility
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @param $label LocalizedFallbackValue
     * @return Facility
     */
    public function addLabel(LocalizedFallbackValue $label): Facility
    {
        if (!$this->labels->contains($label)) {
            $this->labels[] = $label;
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $label
     * @return Facility
     */
    public function removeLabel(LocalizedFallbackValue $label): Facility
    {
        $this->labels->removeElement($label);

        return $this;
    }
}
