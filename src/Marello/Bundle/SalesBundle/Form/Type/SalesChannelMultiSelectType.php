<?php
namespace Marello\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer;

class SalesChannelMultiSelectType extends AbstractType
{
    const NAME = 'marello_sales_saleschannel_multi_select';

    /** @var EntitiesToIdsTransformer */
    protected $modelTransformer;

    /**
     * SalesChannelSelectType constructor.
     *
     * @param EntitiesToIdsTransformer $modelTransformer
     */
    public function __construct(EntitiesToIdsTransformer $modelTransformer)
    {
        $this->modelTransformer = $modelTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'autocomplete_alias' => 'saleschannels',
            'configs'            => [
                'multiple'    => true,
                'placeholder' => 'marello.sales.saleschannel.form.select_saleschannels',
                'allowClear'  => true,
            ],
        ]);
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
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;//
    }
}
