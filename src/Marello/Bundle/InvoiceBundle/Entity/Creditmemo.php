<?php

namespace Marello\Bundle\InvoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity
 */
class Creditmemo extends Invoice
{
    const CREDITMEMO_TYPE = 'creditmemo';

    /**
     * @var string
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $type = self::CREDITMEMO_TYPE;
}
