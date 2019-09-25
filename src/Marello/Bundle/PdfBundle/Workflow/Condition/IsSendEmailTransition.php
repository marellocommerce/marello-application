<?php

namespace Marello\Bundle\PdfBundle\Workflow\Condition;

use Marello\Bundle\PdfBundle\DependencyInjection\Configuration;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\Action\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;

class IsSendEmailTransition extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    const NAME = 'is_send_email_transition';
    const OPTION_CURRENT_TRANSITION = 'current_transition';
    const OPTION_CONFIG_SCOPE = 'config_scope';

    protected $configManager;

    protected $currentTransition;

    protected $configScope;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function initialize(array $options)
    {
        $this->currentTransition = $options[self::OPTION_CURRENT_TRANSITION];
        $this->configScope = $options[self::OPTION_CONFIG_SCOPE];

        return $this;
    }

    public function isConditionAllowed($context)
    {
        $currentTransition = $this->contextAccessor->getValue($context, $this->currentTransition);
        $configScope = $this->contextAccessor->getValue($context, $this->configScope);

        $configKey = sprintf('%s.%s', Configuration::CONFIG_NAME, Configuration::CONFIG_KEY_EMAIL_WORKFLOW_TRANSITION);

        $configuredTransitions = $this->configManager->get($configKey, false, false, $configScope);
        if (is_scalar($configuredTransitions)) {
            $configuredTransitions = [$configuredTransitions];
        }

        return in_array($currentTransition, $configuredTransitions);
    }

    public function getName()
    {
        return self::NAME;
    }

    public function toArray()
    {
        return $this->convertToArray([$this->currentTransition]);
    }

    public function compile($factoryAccessor)
    {
        return $this->convertToPhpCode([$this->currentTransition], $factoryAccessor);
    }
}
