<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntitiesToIdsTransformer;
use Oro\Bundle\MultiCurrencyBundle\Form\Transformer\ArrayToJsonTransformer;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

/**
 * Form type for enabled warehouse grid
 */
class WarehouseGridType extends AbstractType
{
    protected DoctrineHelper $doctrineHelper;
    protected EntitiesToIdsTransformer $modelTransformer;
    protected AclHelper $aclHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        EntitiesToIdsTransformer $modelTransformer,
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->modelTransformer = $modelTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new ArrayToJsonTransformer());
        $builder->addModelTransformer($this->modelTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $whGroup = null;
        if ($form->getParent()->getData() instanceof WarehouseGroup) {
            $whGroup = ($form->getParent()->getData()) ? $form->getParent()->getData()->getId() : null;
        }

        $view->vars['warehouseCollection'] = $this->getWarehouseCollection($whGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'invalid_message' => 'This value is not valid.',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'marello_warehouse_grid';
    }

    protected function getWarehouseCollection(?int $groupId): array
    {
        $qb = $this->getWarehouseCollectionQb($groupId);
        $warehouses = $this->aclHelper->apply($qb)->getResult();
        $warehouseCollection = [];
        /** @var Warehouse $warehouse */
        foreach ($warehouses as $warehouse) {
            $warehouseCollection[$warehouse->getId()] = [
                'id' => $warehouse->getId(),
                'code' => $warehouse->getCode(),
                'name' => $warehouse->getLabel(),
                'isConsolidationWarehouse' => $warehouse->getIsConsolidationWarehouse(),
                'isOrderOnDemandLocation' => $warehouse->isOrderOnDemandLocation(),
                'sortOrderOodLoc' => $warehouse->getSortOrderOodLoc(),
            ];
        }

        return $warehouseCollection;
    }

    /**
     * @param int|null $groupId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getWarehouseCollectionQb(?int $groupId)
    {
        $qb = $this
            ->doctrineHelper
            ->getEntityRepositoryForClass(Warehouse::class)
            ->createQueryBuilder('wh');
        $qb
            ->innerJoin('wh.warehouseType', 'wht')
            ->innerJoin('wh.group', 'whg')
            ->orderBy('wh.label', 'ASC');
        if (!$groupId) {
            $qb
                ->where('whg.system = :isSystem AND wht.name in (:types)')
                ->setParameter('isSystem', true)
                ->setParameter('types', [
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL,
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL,
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL
                ]);
        } else {
            $qb
                ->where('(whg.system = :isSystem AND wht.name in (:types)) OR whg.id = :id')
                ->setParameter('isSystem', true)
                ->setParameter('types', [
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL,
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL,
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL
                ])
                ->setParameter('id', $groupId);
        }
        return $qb;
    }

    /**
     * @param AclHelper $aclHelper
     * @return void
     */
    public function setAclHelper(AclHelper $aclHelper): void
    {
        $this->aclHelper = $aclHelper;
    }
}
