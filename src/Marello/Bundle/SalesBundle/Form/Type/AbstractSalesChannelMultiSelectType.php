<?php

namespace Marello\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

abstract class AbstractSalesChannelMultiSelectType extends AbstractType
{
    /**
     * @var EntitiesToIdsTransformer
     */
    protected $modelTransformer;

    /**
     * @param EntitiesToIdsTransformer $modelTransformer
     */
    public function __construct(EntitiesToIdsTransformer $modelTransformer)
    {
        $this->modelTransformer = $modelTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $value = $event->getData();

                if (empty($value)) {
                    $event->setData([]);
                }
            }
        );

        $builder->addModelTransformer($this->modelTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroJquerySelect2HiddenType::class;
    }
}
