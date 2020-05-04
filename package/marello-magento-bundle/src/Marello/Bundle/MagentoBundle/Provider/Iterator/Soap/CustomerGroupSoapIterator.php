<?php

namespace Marello\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Marello\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

/**
 * @deprecated entire class to be removed
 * Class CustomerGroupSoapIterator
 * @package Marello\Bundle\MagentoBundle\Provider\Iterator\Soap
 */
class CustomerGroupSoapIterator extends AbstractLoadeableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        $result = $this->transport->call(SoapTransport::ACTION_GROUP_LIST);

        return $this->processCollectionResponse($result);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return (array)parent::current();
    }
}
