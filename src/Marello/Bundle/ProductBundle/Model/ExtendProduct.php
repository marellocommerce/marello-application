<?php

namespace Marello\Bundle\ProductBundle\Model;

use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseProduct;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation as Oro;

/**
 * Class ExtendProduct
 * @package Marello\Bundle\ProductBundle\Model
 *
 */
class ExtendProduct extends BaseProduct
{
    /**
     * @Oro\ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $price;


    public function __construct()
    {
    }
}
