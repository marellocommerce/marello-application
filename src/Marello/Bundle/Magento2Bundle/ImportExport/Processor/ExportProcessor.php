<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Marello\Bundle\Magento2Bundle\ImportExport\Translator\TranslatorInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;
use Oro\Bundle\ImportExportBundle\Processor\ContextAwareProcessor;
use Oro\Bundle\ImportExportBundle\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ExportProcessor implements StepExecutionAwareInterface, ContextAwareProcessor
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ContextRegistry
     */
    protected $contextRegistry;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param ContextRegistry $contextRegistry
     */
    public function setContextRegistry(ContextRegistry $contextRegistry)
    {
        $this->contextRegistry = $contextRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        if (!$this->contextRegistry) {
            throw new \InvalidArgumentException('Missing ContextRegistry');
        }

        $this->setImportExportContext($this->contextRegistry->getByStepExecution($this->stepExecution));
    }

    /**
     * @param ContextInterface $context
     * @throws InvalidConfigurationException
     */
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param object $object
     * @return array
     * @throws ExceptionInterface
     */
    public function process($object)
    {
        if (! $this->translator) {
            throw new RuntimeException('[Magento 2] Translator must be injected.');
        }

        if (! $this->serializer) {
            throw new RuntimeException('[Magento 2] Serializer must be injected.');
        }

        $context = $this->context->getConfiguration();
        $translatedObject = $this->translator->translate($object, $context);

        $format = '';
        $data = $this->serializer->encode(
            $this->serializer->normalize($translatedObject, $format, $context),
            $format,
            $context
        );

        return $data;
    }
}
