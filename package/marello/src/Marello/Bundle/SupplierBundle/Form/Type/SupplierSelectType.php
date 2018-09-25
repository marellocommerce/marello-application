<?php
namespace Marello\Bundle\SupplierBundle\Form\Type;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierSelectType extends AbstractType
{
    const NAME = 'marello_supplier_select_form';
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias'    => 'suppliers',
                'entity_class'          => Supplier::class,
                'create_enabled'        => false,
                'grid_name'             => 'marello-supplier-extended-no-actions-grid',
                'grid_parameters'       => [ 'isActive' => 1],
                'configs'               => [
                    'placeholder'               => 'marello.supplier.form.choose_supplier',
                    'result_template_twig'      => 'MarelloSupplierBundle:Supplier:Autocomplete/result.html.twig',
                    'selection_template_twig'   => 'MarelloSupplierBundle:Supplier:Autocomplete/selection.html.twig',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
