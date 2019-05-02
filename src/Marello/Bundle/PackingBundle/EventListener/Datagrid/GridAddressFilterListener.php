<?php

namespace Marello\Bundle\PackingBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;

class GridAddressFilterListener
{
    /** @var DQLNameFormatter */
    protected $dqlNameFormatter;

    /**
     * @param DQLNameFormatter $dqlNameFormatter
     */
    public function __construct(DQLNameFormatter $dqlNameFormatter)
    {
        $this->dqlNameFormatter = $dqlNameFormatter;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        /*
         * Add generated entity name DQL to be selected under alias.
         * Aliases billingName and shippingName are added to query this way.
         */
        $config->offsetAddToArrayByPath('source.query.select', [
            $this->dqlNameFormatter->getFormattedNameDQL(
                'ba',
                'Marello\Bundle\AddressBundle\Entity\MarelloAddress'
            ) . ' as billingName',
            $this->dqlNameFormatter->getFormattedNameDQL(
                'sa',
                'Marello\Bundle\AddressBundle\Entity\MarelloAddress'
            ) . ' as shippingName',
        ]);
    }
}
