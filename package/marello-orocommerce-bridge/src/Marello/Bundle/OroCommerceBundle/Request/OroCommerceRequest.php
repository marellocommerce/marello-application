<?php

namespace Marello\Bundle\OroCommerceBundle\Request;

use Symfony\Component\HttpFoundation\ParameterBag;

class OroCommerceRequest extends ParameterBag implements OroCommerceRequestInterface
{
    const PATH_FIELD = 'path';
    const HEADERS_FIELD = 'headers';
    const PAYLOAD_FIELD = 'payload';

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->get(self::PATH_FIELD);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->get(self::HEADERS_FIELD);
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->get(self::PAYLOAD_FIELD);
    }
}
