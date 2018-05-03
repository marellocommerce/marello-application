<?php

namespace Marello\Bundle\MagentoBundle\Provider\Iterator\Rest;

use Marello\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator;

class LoadableRestIterator extends AbstractLoadeableIterator
{
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
