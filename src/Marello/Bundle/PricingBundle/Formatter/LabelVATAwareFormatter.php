<?php

namespace Marello\Bundle\PricingBundle\Formatter;

use Marello\Bundle\PricingBundle\DependencyInjection\Configuration;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Translation\TranslatorInterface;

class LabelVATAwareFormatter
{
    const TRANSLATION_INCL_VAT = 'marello.pricing.vat.included.label';
    const TRANSLATION_EXCL_VAT = 'marello.pricing.vat.excluded.label';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ConfigManager $configManager
     * @param TranslatorInterface $translator
     */
    public function __construct(ConfigManager $configManager, TranslatorInterface $translator)
    {
        $this->configManager = $configManager;
        $this->translator = $translator;
    }

    /**
     * @param string $originalLabel
     * @return string
     */
    public function getFormattedLabel($originalLabel)
    {
        $isVatEnabled = $this->configManager->get(Configuration::VAT_SYSTEM_CONFIG_PATH);
        if ($isVatEnabled) {
            $suffix = $this->translator->trans(self::TRANSLATION_INCL_VAT);
        } else {
            $suffix = $this->translator->trans(self::TRANSLATION_EXCL_VAT);
        }

        return sprintf('%s %s', $this->translator->trans($originalLabel), $suffix);
    }
}
