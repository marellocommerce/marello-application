<?php

namespace Marello\Bundle\ProductBundle\Api\Processor;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;

class HandleMediaUrl implements ProcessorInterface
{
    private const MEDIA_URL_FIELD_NAME = 'media_url';
    private const CONTENT_FIELD_NAME = 'content';

    public function __construct(
        protected ConfigManager $configManager
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param CustomizeFormDataContext $context
     */
    public function process(ContextInterface $context): void
    {
        $data = $context->getData();

        if (!$this->configManager->get('marello_product.image_use_external_url')) {
            return;
        }

        if (!array_key_exists(self::MEDIA_URL_FIELD_NAME, $data)) {
            return;
        }

        if ($this->hasFileContents($context)) {
            $data[self::CONTENT_FIELD_NAME] = null;
        }

        $context->setData($data);
    }

    /**
     * @param ContextInterface $context
     * @return bool
     */
    private function hasFileContents(ContextInterface $context): bool
    {
        $contentFieldName = $context->getResultFieldName(self::CONTENT_FIELD_NAME);
        $data = $context->getData();

        return $contentFieldName && !empty($data[$contentFieldName]);
    }
}
