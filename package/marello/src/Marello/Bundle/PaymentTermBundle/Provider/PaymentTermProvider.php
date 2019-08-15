<?php

namespace Marello\Bundle\PaymentTermBundle\Provider;

use Marello\Bundle\PaymentTermBundle\DependencyInjection\Configuration;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class PaymentTermProvider
{
    /**
     * @var ConfigManager
     */
    protected $config;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var PaymentTerm
     */
    protected $defaultPaymentTerm;

    /**
     * PaymentTermProvider constructor.
     * @param ConfigManager $config
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(ConfigManager $config, DoctrineHelper $doctrineHelper)
    {
        $this->config = $config;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @return PaymentTerm|null
     */
    public function getDefaultPaymentTerm()
    {
        if ($this->defaultPaymentTerm === null) {
            $this->defaultPaymentTerm = $this->fetchDefaultPaymentTerm();
        }

        return $this->defaultPaymentTerm;
    }

    /**
     * @return PaymentTerm|null
     */
    protected function fetchDefaultPaymentTerm()
    {
        $id = $this->config->get(Configuration::DEFAULT_PAYMENT_TERM_CONFIG_PATH);
        if (!$id) {
            return null;
        }

        return $this->doctrineHelper->getEntityRepositoryForClass(PaymentTerm::class)->find($id);
    }

    /**
     * @return PaymentTerm[]
     */
    public function getPaymentTerms()
    {
        return $this->doctrineHelper->getEntityRepositoryForClass(PaymentTerm::class)->findAll();
    }
}
