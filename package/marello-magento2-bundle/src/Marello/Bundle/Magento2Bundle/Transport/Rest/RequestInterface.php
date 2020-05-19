<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest;

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
