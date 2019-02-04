<?php

namespace Marello\Bundle\InvoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Marello\Bundle\InvoiceBundle\Model\ExtendCreditmemoItem;

/**
 * @ORM\Entity
 */
class CreditmemoItem extends ExtendCreditmemoItem
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
