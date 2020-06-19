<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Request;

interface RequestInterface
{
    /**
     * @return string
     */
    public function getUrn(): string;

    /**
     * @return array
     */
    public function getPayloadData(): array;
}
