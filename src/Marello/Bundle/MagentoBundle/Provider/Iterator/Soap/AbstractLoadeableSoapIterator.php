<?php

namespace Marello\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Marello\Bundle\MagentoBundle\Utils\WSIUtils;
use Marello\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;
use Marello\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator;

abstract class AbstractLoadeableSoapIterator extends AbstractLoadeableIterator
{
    /** @var MagentoSoapTransportInterface */
    protected $transport;

    /**
     * @param MagentoSoapTransportInterface $transport
     */
    public function __construct(MagentoSoapTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Do modifications with response for collection requests
     * Fix issues related to specific results in WSI mode
     *
     * @param mixed $response
     *
     * @return array
     */
    protected function processCollectionResponse($response)
    {
        return WSIUtils::processCollectionResponse($response);
    }
}
