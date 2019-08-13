<?php

namespace Marello\Bundle\PdfBundle\Factory;

use Mpdf\Mpdf;

class PdfWriterFactory
{
    protected $defaultOptions = [];
    protected $passedOptions;
    protected $options;

    public function __construct(array $options = [])
    {
        $this->passedOptions = $options;
    }

    public function create(array $options = [])
    {
        return new Mpdf($this->getOptions($options));
    }

    protected function getOptions(array $options = [])
    {
        if ($this->options === null) {
            $this->options = array_merge($this->defaultOptions, $this->passedOptions);
        }

        return array_merge($this->options, $options);
    }
}
