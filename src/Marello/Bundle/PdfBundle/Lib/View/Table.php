<?php

namespace Marello\Bundle\PdfBundle\Lib\View;

use Doctrine\Common\Collections\ArrayCollection;

class Table
{
    protected $maxHeight;

    protected $headerHeight;

    protected $footerHeight;

    protected $headerEnabled = true;

    protected $footerEnabled = true;

    protected $lines;

    protected $height = 0;

    public function __construct($maxHeight, $headerHeight, $footerHeight)
    {
        $this->maxHeight = $maxHeight;
        $this->headerHeight = $headerHeight;
        $this->footerHeight = $footerHeight;
        $this->lines = new ArrayCollection();
    }

    public function disableHeader()
    {
        $this->headerEnabled = false;

        return $this;
    }

    public function disableFooter()
    {
        $this->footerEnabled = false;

        return $this;
    }

    public function getLines()
    {
        $lines = clone $this->lines;

        for ($i = $this->getHeight(); $i < $this->getMaxHeight(); $i++) {
            $lines[] = new EmptyLine();
        }

        return $lines;
    }

    public function addLine(Line $line)
    {
        $this->lines[] = $line;

        $this->height += $line->getHeight();

        return $this;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getMaxHeight()
    {
        $height = $this->maxHeight;
        if ($this->headerEnabled) {
            $height -= $this->headerHeight;
        }
        if ($this->footerEnabled) {
            $height -= $this->footerHeight;
        }

        return $height;
    }

    public function fitsLine(Line $line)
    {
        $height = $this->getHeight();
        $height += $line->getHeight();

        return $height <= $this->getMaxHeight();
    }
}
