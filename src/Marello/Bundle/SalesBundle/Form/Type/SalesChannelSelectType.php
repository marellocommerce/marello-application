<?php
namespace Marello\Bundle\SalesBundle\Form\Type;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer;

class SalesChannelSelectType extends AbstractType
{
    const NAME = 'marello_sales_saleschannel_select';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'autocomplete_alias'  => 'saleschannels',
                'configs'             => array(
                    'multiple'                   => true,
                    'placeholder'                => 'marello.sales.saleschannel.form.choose_saleschannel',
                    'allowClear'                 => true,
                )
            )
        );
//        $resolver->setDefaults(
//            [
//                'class' => 'MarelloSalesBundle:SalesChannel',
//                'entity_class' => 'MarelloSalesBundle:SalesChannel',
//                'label' => 'marello.sales.saleschannel.entity_label',
//                'multiple' => true,
//            ]
//        );
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
                    $event->setData(array());
                }
            }
        );
        $builder->addModelTransformer(
            new EntitiesToIdsTransformer($this->entityManager, $options['entity_class'])
        );
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
        return self::NAME;
    }
}
