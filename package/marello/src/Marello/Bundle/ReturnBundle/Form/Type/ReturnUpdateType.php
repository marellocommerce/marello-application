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
    const NAME = 'marello_return_update';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('returnItems', ReturnItemCollectionType::NAME, [
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReturnEntity::class,
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }
}
