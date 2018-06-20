<?php

namespace Marello\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Marello\Bundle\MagentoBundle\Utils\WSIUtils;
use Marello\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Marello\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator;

abstract class AbstractLoadeableSoapIterator extends AbstractLoadeableIterator
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /**
     * @param MagentoTransportInterface $transport
     */
    public function __construct(MagentoTransportInterface $transport)
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
