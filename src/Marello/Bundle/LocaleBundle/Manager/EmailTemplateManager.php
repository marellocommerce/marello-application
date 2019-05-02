<?php

namespace Marello\Bundle\LocaleBundle\Manager;

use Marello\Bundle\LocaleBundle\Model\LocaleAwareInterface;
use Marello\Bundle\LocaleBundle\Repository\EmailTemplateTranslatableRepository;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Doctrine\Common\Util\ClassUtils;

class EmailTemplateManager
{
    /** @var  EmailTemplateTranslatableRepository */
    protected $emailTemplateTranslatableRepository;

    /** @var  ConfigManager */
    protected $configManager;

    /**
     * EmailTemplateManager constructor.
     * @param EmailTemplateTranslatableRepository $emailTemplateTranslatableRepository
     * @param ConfigManager $configManager
     */
    public function __construct(
        EmailTemplateTranslatableRepository $emailTemplateTranslatableRepository,
        ConfigManager $configManager
    ) {
        $this->emailTemplateTranslatableRepository  = $emailTemplateTranslatableRepository;
        $this->configManager                        = $configManager;
    }

    /**
     * @param $templateName
     * @param $entity
     * @return null|\Oro\Bundle\EmailBundle\Entity\EmailTemplate
     */
    public function findTemplate($templateName, $entity)
    {
        $template = null;

        /*
         * 1. Try to get translated version.
         */
        $template = $this->findEntityLocaleTemplate($templateName, $entity);

        /*
         * 2. If translation not found or not supported, try to get sales channel's default locale.
         */
        if ($template == null) {
            $template = $this->findSalesChannelDefaultLocaleTemplate($templateName, $entity);
        }

        /*
         * 3. If translation not found or not supported, try to get default template.
         */
        if ($template == null) {
            $template = $this->findDefaultTemplate($templateName, $entity);
        }
        
        return $template;
    }

    /**
     * @param $templateName
     * @param $entity
     * @return null|\Oro\Bundle\EmailBundle\Entity\EmailTemplate
     */
    protected function findEntityLocaleTemplate($templateName, $entity)
    {
        if ($entity instanceof LocaleAwareInterface && $entity->getLocale() !== null) {
            if ($this->isSupportedLocale($entity->getLocale())) {
                return $this->emailTemplateTranslatableRepository
                    ->findOneByNameAndLocale($templateName, $entity->getLocale());
            }
        }

        return null;
    }

    /**
     * @param $templateName
     * @param $entity
     * @return null|\Oro\Bundle\EmailBundle\Entity\EmailTemplate
     */
    protected function findSalesChannelDefaultLocaleTemplate($templateName, $entity)
    {
        if (method_exists($entity, 'getSalesChannel')) {
            $salesChannel = $entity->getSalesChannel();

            if ($salesChannel instanceof LocaleAwareInterface && $salesChannel->getLocale() !== null) {
                return $this->emailTemplateTranslatableRepository
                    ->findOneByNameAndLocale($templateName, $salesChannel->getLocale());
            }
        }

        return null;
    }

    /**
     * @param $templateName
     * @param $entity
     * @return null|\Oro\Bundle\EmailBundle\Entity\EmailTemplate
     */
    protected function findDefaultTemplate($templateName, $entity)
    {
        $entityName = ClassUtils::getRealClass(get_class($entity));

        return $this->emailTemplateTranslatableRepository->findOneBy(
            ['name' => $templateName, 'entityName' => $entityName]
        );
    }

    /**
     * @param $locale
     * @return bool
     */
    protected function isSupportedLocale($locale)
    {
        //check if entity's locale is supported
        $supportedLocales = $this->configManager->get('oro_locale.languages');
        if (in_array($locale, $supportedLocales)) {
            return true;
        }

        //check if entity locale is english, as it's saved as "en" string in config, not usual iso code locale
        $lang = substr($locale, 0, 2);
        if ($lang === "en" && in_array($lang, $supportedLocales)) {
            return true;
        }

        return false;
    }
}
