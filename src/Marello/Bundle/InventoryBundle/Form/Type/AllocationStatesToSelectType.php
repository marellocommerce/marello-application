<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumChoiceType;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer;

use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;

class AllocationStatesToSelectType extends AbstractType
{
    const NAME = 'marello_inventory_allocation_states_to_select';

    public function __construct(
        protected DoctrineHelper $doctrineHelper
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $className = ExtendHelper::buildEnumValueClassName(AllocationStateStatusInterface::ALLOCATION_STATE_ENUM_CODE);
        /** @var EntityManager $em */
        $em = $this->doctrineHelper->getEntityManagerForClass($className);

        $entitiesToIdsTransformer = new EntitiesToIdsTransformer($em, $className);
        $builder->addModelTransformer(new ReversedTransformer($entitiesToIdsTransformer));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'enum_code' => AllocationStateStatusInterface::ALLOCATION_STATE_ENUM_CODE,
                'multiple' => true,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('enum')
                        ->orderBy('enum.name', 'ASC');
                }
            ]
        );
    }

    public function getParent()
    {
        return EnumChoiceType::class;
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
        return static::NAME;
    }
}
