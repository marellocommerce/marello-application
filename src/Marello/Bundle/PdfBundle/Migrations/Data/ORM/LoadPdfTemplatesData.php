<?php

namespace Marello\Bundle\PdfBundle\Migrations\Data\ORM;

use Marello\Bundle\PdfBundle\Migrations\Data\ORM\AbstractPdfFixture;

class LoadPdfTemplatesData extends AbstractPdfFixture
{
    /**
     * Return path to email templates
     *
     * @return string
     */
    public function getPdfsDir()
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@MarelloPdfBundle/Migrations/Data/ORM/data/pdfs');
    }
}
