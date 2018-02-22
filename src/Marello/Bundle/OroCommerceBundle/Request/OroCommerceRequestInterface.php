<?php

namespace Marello\Bundle\OroCommerceBundle\Request;

interface OroCommerceRequestInterface
{
    /**
     * @return string
     */
    public function getPath();

    /**
     * @return array
     */
    public function getHeaders();
    
    /**
     * @return mixed
     */
    public function getPayload();
}
