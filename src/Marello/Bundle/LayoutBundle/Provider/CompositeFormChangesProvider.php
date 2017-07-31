<?php

namespace Marello\Bundle\LayoutBundle\Provider;

use Marello\Bundle\LayoutBundle\Context\FormChangeContextInterface;

class CompositeFormChangesProvider implements FormChangesProviderInterface
{
    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var string
     */
    protected $requiredDataClass;

    /**
     * @var array
     */
    protected $requiredFields = [];


    /**
     * @param string $class
     * @return $this
     */
    public function setRequiredDataClass($class)
    {
        $this->requiredDataClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequiredFields(array $fields = null)
    {
        $this->requiredFields = $fields;
        
        return $this;
    }

    /**
     * @param FormChangesProviderInterface $provider
     * @param string $dataClass
     * @param string $field
     * @return $this
     */
    public function addProvider(FormChangesProviderInterface $provider, $dataClass, $field)
    {
        $this->providers[$dataClass][$field] = $provider;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function processFormChanges(FormChangeContextInterface $context)
    {
        if ($this->requiredDataClass === null) {
            throw new \LogicException('requiredDataClass should be specified');
        }

        if (array_key_exists($this->requiredDataClass, $this->providers)) {
            /** @var FormChangesProviderInterface $provider */
            foreach ($this->providers[$this->requiredDataClass] as $field => $provider) {
                if (count($this->requiredFields) > 0) {
                    if (in_array($field, $this->requiredFields, true)) {
                        $provider->processFormChanges($context);
                    }
                } else {
                    $provider->processFormChanges($context);
                }
            }
        }
    }
}
