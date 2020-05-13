<?php

namespace Marello\Bundle\PdfBundle\Exception;

class PaperSizeNotSetException extends \RuntimeException
{
    public function __construct($key, $paperSize)
    {
        parent::__construct(sprintf('Paper size "%s" not set for value "%s"', $paperSize, $key));
    }
}
