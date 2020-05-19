<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest;

class Request implements RequestInterface
{
    /**
     * @var string
     */
    protected $urn;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $urn
     * @param array $data
     */
    public function __construct(string $urn, array $data)
    {
        $this->urn = $urn;
        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrn(): string
    {
        return $this->urn;
    }

    /**
     * {@inheritDoc}
     */
    public function getPayloadData(): array
    {
        return $this->data;
    }
}
