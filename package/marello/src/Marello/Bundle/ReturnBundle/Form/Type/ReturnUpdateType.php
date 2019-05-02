<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReturnUpdateType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_return_update';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('returnItems', ReturnItemCollectionType::class, [
            'update' => true,
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var ReturnEntity $return */
            $return = $event->getData();

            /*
             * Remove all return items with returned quantity equal to 0.
             */
            $return->getReturnItems()
                ->filter(function (ReturnItem $returnItem) {
                    return !$returnItem->getQuantity();
                })
                ->map(function (ReturnItem $returnItem) use ($return) {
                    $return->removeReturnItem($returnItem);
                });

            $event->setData($return);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReturnEntity::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
