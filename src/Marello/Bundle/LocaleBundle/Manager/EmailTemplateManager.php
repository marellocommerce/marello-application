<?php

namespace Marello\Bundle\LocaleBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Marello\Bundle\LocaleBundle\Model\LocalizationAwareInterface;
use Marello\Bundle\LocaleBundle\Provider\EntityLocalizationProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class EmailTemplateManager
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var  ConfigManager */
    protected $configManager;

    /** @var  EntityLocalizationProviderInterface */
    protected $entityLocalizationProvider;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ConfigManager $configManager
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ConfigManager $configManager
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->configManager = $configManager;
    }

    /**
     * @param EntityLocalizationProviderInterface $entityLocalizationProvider
     */
    public function setEntityLocalizationProvider(EntityLocalizationProviderInterface $entityLocalizationProvider)
    {
        $this->entityLocalizationProvider = $entityLocalizationProvider;
    }

    /**
     * @param $templateName
     * @param $entity
     * @return null|\Oro\Bundle\EmailBundle\Entity\EmailTemplate
     */
    public function findTemplate($templateName, $entity)
    {
        $template = $this->findEntityLocalizationTemplate($templateName, $entity);

        /*
         * If translation not found or not supported, try to get default template.
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
    public function findEntityLocalizationTemplate($templateName, $entity)
    {
        if ($entity instanceof LocalizationAwareInterface) {
            $localization = $this->entityLocalizationProvider->getLocalization($entity);
            $entityName = ClassUtils::getRealClass(get_class($entity));
            $criteria = new EmailTemplateCriteria($templateName, $entityName);

            return $this
                ->getEmailTemplateRepository()
                ->findOneLocalized($criteria, $localization->getLanguageCode());
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

        return $this
            ->getEmailTemplateRepository()
            ->findOneBy(['name' => $templateName, 'entityName' => $entityName]);
    }

    /**
     * Get email template repository to find localized versions of templates
     * @return EntityRepository
     */
    private function getEmailTemplateRepository()
    {
        return $this->doctrineHelper
            ->getEntityRepository(EmailTemplate::class);
    }
}
