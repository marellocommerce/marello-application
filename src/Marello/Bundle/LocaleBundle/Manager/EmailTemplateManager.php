<?php

namespace Marello\Bundle\LocaleBundle\Manager;

use Marello\Bundle\LocaleBundle\Model\LocaleAwareInterface;
use Marello\Bundle\LocaleBundle\Provider\EntityLocalizationProviderInterface;
use Marello\Bundle\LocaleBundle\Repository\EmailTemplateTranslatableRepository;

class EmailTemplateManager
{
    /**
     * @var  EmailTemplateTranslatableRepository
     */
    protected $emailTemplateTranslatableRepository;

    /**
     * @var  EntityLocalizationProviderInterface
     */
    protected $entityLocalizationProvider;

    /**
     * @param EmailTemplateTranslatableRepository $emailTemplateTranslatableRepository
     * @param EntityLocalizationProviderInterface $entityLocalizationProvider
     */
    public function __construct(
        EmailTemplateTranslatableRepository $emailTemplateTranslatableRepository,
        EntityLocalizationProviderInterface $entityLocalizationProvider
    ) {
        $this->emailTemplateTranslatableRepository = $emailTemplateTranslatableRepository;
        $this->entityLocalizationProvider = $entityLocalizationProvider;
    }

    /**
     * @param $templateName
     * @param $entity
     * @return null|\Oro\Bundle\EmailBundle\Entity\EmailTemplate
     */
    public function findTemplate($templateName, $entity)
    {
        if ($entity instanceof LocaleAwareInterface) {
            $localization = $this->entityLocalizationProvider->getLocalization($entity);
            return $this->emailTemplateTranslatableRepository
                ->findOneByNameAndLocale($templateName, $localization->getLanguageCode());
        }
        
        return null;
    }
}
