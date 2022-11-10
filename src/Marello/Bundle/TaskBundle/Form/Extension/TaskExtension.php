<?php

namespace Marello\Bundle\TaskBundle\Form\Extension;

use Marello\Bundle\TaskBundle\Form\Type\GroupSelectType;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\TaskBundle\Form\Type\TaskType;
use Oro\Bundle\UserBundle\Form\Type\UserSelectType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TaskExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [TaskType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'type',
                EnumSelectType::class,
                [
                    'label' => 'marello.task.type.label',
                    'enum_code' => 'task_type',
                    'required' => true,
                    'constraints' => [new Assert\NotNull()],
                    'dynamic_fields_ignore_exception' => true,
                ]
            )
            ->add(
                'assignedToUser',
                UserSelectType::class,
                [
                    'label' => 'marello.task.assigned_to_user.label',
                    'dynamic_fields_ignore_exception' => true,
                ]
            )
            ->add(
                'assignedToGroup',
                GroupSelectType::class,
                [
                    'label' => 'marello.task.assigned_to_group.label',
                    'dynamic_fields_ignore_exception' => true,
                ]
            );
    }
}
