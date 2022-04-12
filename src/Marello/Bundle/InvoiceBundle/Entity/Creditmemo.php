<?php

namespace Marello\Bundle\InvoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

use Marello\Bundle\InvoiceBundle\Model\ExtendCreditmemo;

/**
 * @ORM\Entity
 */
class Creditmemo extends ExtendCreditmemo
{
    const CREDITMEMO_TYPE = 'creditmemo';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     */
    protected $invoiceType = self::CREDITMEMO_TYPE;

    /**
     * @var Collection|CreditmemoItem[]
     *
     * @ORM\OneToMany(targetEntity="CreditmemoItem", mappedBy="invoice", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Oro\ConfigField(
     *      defaultValues={
     *          "email"={
     *              "available_in_template"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $items;
}
