<?php

namespace Marello\Bundle\CatalogBundle\Form\Type;

use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\CatalogBundle\Formatter\CategoryCodeFormatter;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    const NAME = 'marello_catalog_category';

    /**
     * @var CategoryCodeFormatter
     */
    private $codeFormatter;

    /**
     * @param CategoryCodeFormatter $codeFormatter
     */
    public function __construct(CategoryCodeFormatter $codeFormatter)
    {
        $this->codeFormatter = $codeFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('code', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add(
                'appendProducts',
                EntityIdentifierType::NAME,
                [
                    'class'    => Product::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeProducts',
                EntityIdentifierType::NAME,
                [
                    'class'    => Product::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            );
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['code'])) {
            $data['code'] = $this->codeFormatter->format($data['code']);
            $event->setData($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'intention' => 'category',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
