<?php

namespace Marello\Bundle\Magento2Bundle\Model;

use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @method SalesChannel|null getSalesChannel()
 * @method Website setSalesChannel(SalesChannel $salesChannel = null)
 *
 * @method ArrayCollection getMagento2Websites()
 * @method Website addMagento2Websit(Website $magento2Website)
 * @method Website removeMagento2Websit(Website $magento2Website)
 */
class ExtendWebsite
{
    /**
     * Constructor
     *
     * The real implementation of this method is auto generated.
     *
     * IMPORTANT: If the derived class has own constructor it must call parent constructor.
     */
    public function __construct()
    {
    }
}
