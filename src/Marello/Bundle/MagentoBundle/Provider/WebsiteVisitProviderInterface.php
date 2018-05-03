<?php

namespace Marello\Bundle\MagentoBundle\Provider;

interface WebsiteVisitProviderInterface
{
    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getSiteVisitsValues($dateRange);
}
