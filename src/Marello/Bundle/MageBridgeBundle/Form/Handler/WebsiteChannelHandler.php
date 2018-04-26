<?php

namespace Marello\Bundle\MageBridgeBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\MageBridgeBundle\Form\Type\WebsiteSalesChannelType;
use Symfony\Component\Form\Form;

use Oro\Bundle\ConfigBundle\Config\ConfigChangeSet;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiCurrencyBundle\Entity\Repository\RateRepository;
use Oro\Bundle\MultiCurrencyBundle\Form\Type\CurrencyRatesType;
use Oro\Bundle\MultiCurrencyBundle\Provider\RateProvider;

use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class WebsiteChannelHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var RateProvider
     */
    protected $rateProvider;

    /**
     * @param EntityManager $entityManager
     * @param RateProvider  $rateProvider
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
//        $this->rateProvider = $rateProvider;
    }

    /**
     * @param ConfigManager   $manager
     * @param ConfigChangeSet $changeSet
     * @param Form            $form
     */
    public function process(ConfigManager $manager, ConfigChangeSet $changeSet, Form $form)
    {
        $submittedData = $form->get(WebsiteSalesChannelType::CONFIG_FORM_NAME)->getParent()->getData();

        file_put_contents('/app/app/logs/debug.log', print_r($submittedData, true), FILE_APPEND | LOCK_EX);
        file_put_contents('/app/app/logs/debug.log', print_r(__METHOD__ .'###'. __LINE__, true), FILE_APPEND | LOCK_EX);


    }
}
