<?php

namespace Marello\Bundle\PdfBundle\Provider;

interface TableProviderInterface
{
    public function supports($entity);

    public function getTables($entity);
}
