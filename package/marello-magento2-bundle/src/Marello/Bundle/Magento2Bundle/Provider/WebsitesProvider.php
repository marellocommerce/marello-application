<?php

namespace Marello\Bundle\Magento2Bundle\Provider;

use Marello\Bundle\Magento2Bundle\Transport\Magento2TransportInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebsitesProvider
{
    /** @var  TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Magento2TransportInterface $transport
     * @return array
     */
    public function getFormattedWebsites(Magento2TransportInterface $transport): array
    {
        return $transport->getWebsites();
    }
}
