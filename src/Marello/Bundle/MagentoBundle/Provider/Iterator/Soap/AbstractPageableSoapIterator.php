<?php

namespace Marello\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Marello\Bundle\MagentoBundle\Entity\Website;
use Marello\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableIterator;
use Marello\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;
use Marello\Bundle\MagentoBundle\Utils\WSIUtils;

abstract class AbstractPageableSoapIterator extends AbstractPageableIterator
{
    /** @var MagentoSoapTransportInterface */
    protected $transport;

    /**
     * @param MagentoSoapTransportInterface $transport
     * @param array                         $settings
     */
    public function __construct(MagentoSoapTransportInterface $transport, array $settings)
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
