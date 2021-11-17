<?php

namespace Marello\Bundle\InvoiceBundle\Pdf\Logo;

use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Marello\Bundle\PdfBundle\Provider\RenderParameterProviderInterface;

class InvoiceLogoRenderParameterProvider implements RenderParameterProviderInterface
{
    const OPTION_KEY = 'sales_channel';

    protected $logoProvider;

    public function __construct(InvoiceLogoPathProvider $logoProvider)
    {
        $this->logoProvider = $logoProvider;
    }

    public function supports($entity, array $options)
    {
        return $entity instanceof AbstractInvoice
            || isset($options[self::OPTION_KEY]);
    }

    public function getParams($entity, array $options)
    {
        if ($entity instanceof AbstractInvoice) {
            $salesChannel = $entity->getSalesChannel();
        } else {
            $salesChannel = $options[self::OPTION_KEY];
        }

        return [
            'logo' => $this->logoProvider->getInvoiceLogo($salesChannel, true),
            'logo_width' => $this->logoProvider->getInvoiceLogoWidth($salesChannel),
        ];
    }
}
