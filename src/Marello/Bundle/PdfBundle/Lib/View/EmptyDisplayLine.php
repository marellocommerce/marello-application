<?php

namespace Marello\Bundle\PdfBundle\Lib\View;

class EmptyDisplayLine implements \ArrayAccess
{
    public function offsetExists($key)
    {
        return true;
    }

    public function offsetGet($key)
    {
        return null;
    }

    public function offsetSet($key, $value)
    {
        throw new \BadMethodCallException(sprintf('Cannot set values on %s object', __CLASS__));
    }

    public function offsetUnset($key)
    {
        throw new \BadMethodCallException(sprintf('Cannot set values on %s object', __CLASS__));
    }
}
