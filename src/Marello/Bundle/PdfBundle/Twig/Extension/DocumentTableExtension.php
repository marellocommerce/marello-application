<?php

namespace Marello\Bundle\PdfBundle\Twig\Extension;

use Marello\Bundle\PdfBundle\Provider\DocumentTableProvider;

class DocumentTableExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('get_document_tables', [$this->tableProvider, 'getTables']),
        ];
    }

    public function getName()
    {
        return self::NAME;
    }
}
