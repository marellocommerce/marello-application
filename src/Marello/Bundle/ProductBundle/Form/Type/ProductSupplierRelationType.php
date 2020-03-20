<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\SupplierBundle\Form\Type\SupplierSelectType;
use Oro\Bundle\CurrencyBundle\Model\LocaleSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProductSupplierRelationType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_supplier_relation_form';

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'supplier',
                SupplierSelectType::class,
                [
                    'required'       => true,
                    'label'          => 'marello.supplier.entity_label',
                    'create_enabled' => false,
                ]
            )
            ->add('quantityOfUnit')
            ->add('priority')
            ->add('cost')
            ->add('canDropship')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => ProductSupplierRelation::class,
            'constraints'        => [new Valid()],
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->get('supplier')->getData();
        if ($data instanceof Supplier) {
            $view->vars['currency'] = $this->localeSettings->getCurrencySymbolByCurrency($data->getCurrency());
        } else {
            $view->vars['currency'] = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
