<?php

namespace Marello\Bundle\Magento2Bundle\Provider;

use Marello\Bundle\Magento2Bundle\ImportExport\Converter\WebsiteDataConverter;
use Marello\Bundle\Magento2Bundle\Transport\Magento2TransportInterface;

class WebsitesProvider
{
    /**
     * @param Magento2TransportInterface $transport
     * @return array
     * [
     *     int <website_id> => string <website_name>
     * ]
     */
    public function getFormattedWebsites(Magento2TransportInterface $transport): array
    {
        $websiteIdsWithNames = [];
        foreach ($transport->getWebsites() as $website) {
            $websiteId = $website[WebsiteDataConverter::ID_COLUMN_NAME];
            $websiteName = $website[WebsiteDataConverter::NAME_COLUMN_NAME];
            $websiteIdsWithNames[$websiteId] = $websiteName;
        }

        return $websiteIdsWithNames;
    }
}
