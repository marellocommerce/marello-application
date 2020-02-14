<?php

namespace Marello\Bundle\PdfBundle\Provider;

class DocumentTableProvider
{
    protected $providers = [];

    public function addProvider(TableProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    public function getTables($entity)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($entity)) {
                return $provider->getTables($entity);
            }
        }

        return [];
    }
}
