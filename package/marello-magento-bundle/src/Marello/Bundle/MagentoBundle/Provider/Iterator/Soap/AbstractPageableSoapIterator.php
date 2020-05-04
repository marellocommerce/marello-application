<?php

namespace Marello\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Marello\Bundle\MagentoBundle\Entity\Website;
use Marello\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableIterator;
use Marello\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Marello\Bundle\MagentoBundle\Utils\WSIUtils;

abstract class AbstractPageableSoapIterator extends AbstractPageableIterator
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /**
     * @param MagentoTransportInterface $transport
     * @param array                         $settings
     */
    public function __construct(MagentoTransportInterface $transport, array $settings)
    {
        parent::__construct($transport, $settings);
    }

    /**
     * @param mixed $response
     *
     * @return array
     */
    protected function processCollectionResponse($response)
    {
        return WSIUtils::processCollectionResponse($response);
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function convertResponseToMultiArray($response)
    {
        return WSIUtils::convertResponseToMultiArray($response);
    }
}
