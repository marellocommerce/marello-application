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
use Oro\Bundle\EmailBundle\Provider\EmailTemplateContentProvider;
use Oro\Bundle\EmailBundle\Model\EmailTemplate as EmailTemplateModel;

class EmailTemplateManager
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var  ConfigManager */
    protected $configManager;

    /** @var  EntityLocalizationProviderInterface */
    protected $entityLocalizationProvider;

    /** @var EmailTemplateContentProvider $emailTemplateContentProvider */
    protected $emailTemplateContentProvider;

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
     * @param EmailTemplateContentProvider $emailTemplateContentProvider
     */
    public function setEmailTemplateContentProvider(EmailTemplateContentProvider $emailTemplateContentProvider)
    {
        $this->emailTemplateContentProvider = $emailTemplateContentProvider;
    }

    /**
     * @param $templateName
     * @param $entity
     * @return \Extend\Entity\EX_OroEmailBundle_EmailTemplate|null|EmailTemplate
     */
    public function findTemplate($templateName, $entity)
    {
        $template = $this->findEntityLocalizationTemplate($templateName, $entity);

        /*
         * If translation not found or not supported, try to get default template.
         */
        if ($template === null) {
            $template = $this->findDefaultTemplate($templateName, $entity);
        }
        
        return $template;
    }

    /**
     * @param $templateName
     * @param $entity
     * @return null|object|EmailTemplate
     */
    public function findEntityLocalizationTemplate($templateName, $entity)
    {
        if ($entity instanceof LocalizationAwareInterface) {
            $entityName = ClassUtils::getRealClass(get_class($entity));
            $criteria = new EmailTemplateCriteria($templateName, $entityName);
            $emailTemplate = $this
                ->getEmailTemplateRepository()
                ->findWithLocalizations($criteria);

            return $emailTemplate;
        }

        return null;
    }

    /**
     * @param $templateName
     * @param $entity
     * @return null|object|EmailTemplate
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

    /**
     * @param EmailTemplate $template
     * @param $entity
     * @return null|EmailTemplateModel
     */
    public function getLocalizedModel(EmailTemplate $template, $entity)
    {
        if ($entity instanceof LocalizationAwareInterface) {
            $localization = $this->entityLocalizationProvider->getLocalization($entity);
            return $this->emailTemplateContentProvider->getLocalizedModel($template, $localization);
        }

        return null;
    }
}
