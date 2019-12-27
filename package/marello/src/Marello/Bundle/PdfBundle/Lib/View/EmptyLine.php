<?php

namespace Marello\Bundle\PdfBundle\Lib\View;

class EmptyLine extends Line
{
    public function __construct()
    {
        parent::__construct();
    }

    public function offsetExists($key)
    {
        return true;
    }

    public function offsetGet($key)
    {
        return [null];
    }

    public function offsetSet($key, $value)
    {
        throw new \BadMethodCallException(sprintf('Cannot set values on %s object', __CLASS__));
    }

    public function offsetUnset($key)
    {
        throw new \BadMethodCallException(sprintf('Cannot set values on %s object', __CLASS__));
    }

    public function getHeight()
    {
        return 1;
    }

    public function getDisplayLines()
    {
        return [new EmptyDisplayLine()];
    }

    public function isEmpty()
    {
        return true;
    }
}
