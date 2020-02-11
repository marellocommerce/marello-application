<?php

namespace Marello\Bundle\PdfBundle\Form\Configurator;

use Marello\Bundle\PdfBundle\DependencyInjection\Configuration as PdfConfig;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Form\Handler\ConfigHandler;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration as LocaleConfig;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizationSelectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Localization form configurator
 */
class DocumentConfigurator
{
    /**
     * @var ConfigHandler
     */
    private $configHandler;

    /**
     * @param ConfigHandler $configHandler
     */
    public function __construct(ConfigHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    /**
     * @param FormBuilderInterface $builder
     */
    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                if ($event->getData() === null) {
                    return;
                }

                $form = $event->getForm();
                $data = $event->getData();
                $configManager = $this->configHandler->getConfigManager();
                if ($data[PdfConfig::getFieldKeyByName(PdfConfig::CONFIG_KEY_LOCALIZATION)]['value'] === null) {
                    $data[PdfConfig::getFieldKeyByName(PdfConfig::CONFIG_KEY_LOCALIZATION)]['value'] =
                        $configManager->get(
                        LocaleConfig::getConfigKeyByName(LocaleConfig::DEFAULT_LOCALIZATION)
                    );
                    $event->setData($data);
                }

                $this->setEnabledLocalizations($form, $configManager);
            }
        );
    }

    /**
     * @param FormInterface $form
     * @param ConfigManager $configManager
     */
    private function setEnabledLocalizations(FormInterface $form, ConfigManager $configManager): void
    {
        $form = $form->get(PdfConfig::getFieldKeyByName(PdfConfig::CONFIG_KEY_LOCALIZATION));

        $options = $form->get('value')
            ->getConfig()
            ->getOptions();

        $options[LocaleConfig::ENABLED_LOCALIZATIONS] = $configManager->get(
            LocaleConfig::getConfigKeyByName(LocaleConfig::ENABLED_LOCALIZATIONS)
        );

        $form->add('value', LocalizationSelectionType::class, $options);
    }
}
