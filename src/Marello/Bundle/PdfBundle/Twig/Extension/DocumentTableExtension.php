<?php

namespace Marello\Bundle\PdfBundle\Twig\Extension;

use Marello\Bundle\PdfBundle\Provider\DocumentTableProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DocumentTableExtension extends AbstractExtension
{
    const NAME = 'marello_document_table';

    protected $tableProvider;

    public function __construct(DocumentTableProvider $tableProvider)
    {
        $this->tableProvider = $tableProvider;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_document_tables', [$this->tableProvider, 'getTables']),
        ];
    }

    public function getName()
    {
        return self::NAME;
    }
}
