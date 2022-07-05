<?php

namespace Marello\Bundle\PdfBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    const CONFIG_NAME = 'marello_pdf';

    const CONFIG_KEY_PAPER_SIZE = 'paper_size';
    const CONFIG_KEY_LOCALIZATION = 'localization';
    const CONFIG_KEY_COMPANY_ADDRESS = 'company_address';
    const CONFIG_KEY_COMPANY_EMAIL = 'company_email';
    const CONFIG_KEY_LOGO = 'logo';
    const CONFIG_KEY_LOGO_WIDTH = 'logo_width';
    const CONFIG_KEY_COMPANY_PHONE = 'company_phone';
    const CONFIG_KEY_COMPANY_BANK = 'company_bank';
    const CONFIG_KEY_COMPANY_COC = 'company_coc';
    const CONFIG_KEY_EMAIL_WORKFLOW_TRANSITION = 'email_workflow_transition';
    const CONFIG_KEY_EMAIL_SENDER_NAME = 'email_sender_name';
    const CONFIG_KEY_EMAIL_SENDER_EMAIL = 'email_sender_email';
    const CONFIG_KEY_EMAIL_BCC = 'email_bcc';

    const PAPER_SIZE_A4 = 'a4';
    const PAPER_SIZE_LETTER = 'letter';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::CONFIG_NAME);
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                self::CONFIG_KEY_PAPER_SIZE => ['value' => self::PAPER_SIZE_A4],
                self::CONFIG_KEY_LOCALIZATION => ['value' => null],
                self::CONFIG_KEY_COMPANY_ADDRESS => ['value' => null],
                self::CONFIG_KEY_COMPANY_EMAIL => ['value' => null],
                self::CONFIG_KEY_LOGO => ['value' => null],
                self::CONFIG_KEY_LOGO_WIDTH => ['value' => null],
                self::CONFIG_KEY_COMPANY_PHONE => ['value' => null],
                self::CONFIG_KEY_COMPANY_BANK => ['value' => null],
                self::CONFIG_KEY_COMPANY_COC => ['value' => null],
                self::CONFIG_KEY_EMAIL_WORKFLOW_TRANSITION => ['value' => null],
                self::CONFIG_KEY_EMAIL_SENDER_NAME => ['value' => null],
                self::CONFIG_KEY_EMAIL_SENDER_EMAIL => ['value' => null],
                self::CONFIG_KEY_EMAIL_BCC => ['value' => null],
            ]
        );

        return $treeBuilder;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getConfigKeyByName(string $name): string
    {
        return TreeUtils::getConfigKey(self::CONFIG_NAME, $name, ConfigManager::SECTION_MODEL_SEPARATOR);
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getFieldKeyByName(string $name): string
    {
        return TreeUtils::getConfigKey(self::CONFIG_NAME, $name, ConfigManager::SECTION_VIEW_SEPARATOR);
    }
}
