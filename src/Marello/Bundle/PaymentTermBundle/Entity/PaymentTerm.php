<?php

namespace Marello\Bundle\PaymentTermBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(
 *     name="marello_payment_term",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"code"})
 *     }
 * )
 * @Oro\Config(
 *      routeName="marello_paymentterm_paymentterm_index",
 *      routeView="marello_paymentterm_paymentterm_view",
 *      routeUpdate="marello_paymentterm_paymentterm_update",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-usd"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "form"={
 *              "form_type"="Marello\Bundle\PaymentTermBundle\Form\Type\PaymentTermSelectType",
 *              "grid_name"="marello-payment-terms-select-grid",
 *          }
 *      }
 * )
 */
class PaymentTerm implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Oro\ConfigField(
     *     defaultValues={
     *         "importexport"={
     *              "excluded"=true
     *         }
     *     }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32)
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "identity"=true,
     *              "order"=10,
     *          }
     *      }
     * )
     */
    protected $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="term", type="integer")
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=20,
     *          }
     *      }
     * )
     */
    protected $term;

    /**
     * @var Collection|LocalizedFallbackValue[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="marello_payment_term_labels",
     *      joinColumns={
     *          @ORM\JoinColumn(name="paymentterm_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     * @Oro\ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=30,
     *              "full"=true,
     *              "fallback_field"="string"
     *          }
     *      }
     * )
     */
    protected $labels;

    public function __construct()
    {
        $this->labels = new ArrayCollection();
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
     * @return PaymentTerm
     */
    public function setCode(string $code): PaymentTerm
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return int
     */
    public function getTerm(): ?int
    {
        return $this->term;
    }

    /**
     * @param int $term
     * @return PaymentTerm
     */
    public function setTerm(int $term): PaymentTerm
    {
        $this->term = $term;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getLabels(): Collection
    {
        return $this->labels;
    }

    /**
     * @param $label LocalizedFallbackValue
     * @return PaymentTerm
     */
    public function addLabel(LocalizedFallbackValue $label): PaymentTerm
    {
        if (!$this->labels->contains($label)) {
            $this->labels[] = $label;
        }

        return $this;
    }

    public function removeLabel(LocalizedFallbackValue $label): PaymentTerm
    {
        $this->labels->removeElement($label);

        return $this;
    }
}
