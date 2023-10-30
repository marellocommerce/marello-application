<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

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

class AllocationConsolidationExclusionSelectType extends AbstractType
{
    const NAME = 'marello_inventory_consolidation_exclusion';

    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $className = ExtendHelper::buildEnumValueClassName(AllocationStateStatusInterface::ALLOCATION_STATE_ENUM_CODE);
        /** @var EntityManager $em */
        $em = $this->doctrineHelper->getEntityManagerForClass($className);

        $entitiesToIdsTransformer = new EntitiesToIdsTransformer($em, $className);
        $builder->addModelTransformer(new ReversedTransformer($entitiesToIdsTransformer));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'enum_code' => AllocationStateStatusInterface::ALLOCATION_STATE_ENUM_CODE,
                'multiple' => true,
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('enum');
                    return $qb
                        ->where($qb->expr()->neq('enum.id', '?1'))
                        ->setParameter('1', AllocationStateStatusInterface::ALLOCATION_STATE_AVAILABLE)
                        ->orderBy('enum.name', 'ASC')
                        ;
                }
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EnumChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return static::NAME;
    }
}
