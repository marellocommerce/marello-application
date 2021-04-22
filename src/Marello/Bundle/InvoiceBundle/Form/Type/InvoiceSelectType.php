<?php

namespace Marello\Bundle\InvoiceBundle\Form\Type;

use Marello\Bundle\InvoiceBundle\Entity\AbstractInvoice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceSelectType extends AbstractType
{
    const BLOCK_PREFIX = 'invoice_select';

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => AbstractInvoice::class,
            'choice_label' =>  function (AbstractInvoice $invoice) {
                return sprintf('%s: %s', $invoice->getInvoiceType(), $invoice->getInvoiceNumber());
            },
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
