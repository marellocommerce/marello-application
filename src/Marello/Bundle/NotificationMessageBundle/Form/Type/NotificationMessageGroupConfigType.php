<?php

namespace Marello\Bundle\NotificationMessageBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\UserBundle\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationMessageGroupConfigType extends AbstractType
{
    public const NAME = 'marello_notificationmessage_group_config';

    private array $types = [];
    private array $sources = [];
    private array $groupChoices = [];

    public function __construct(
        private ManagerRegistry $registry
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->getSources() as $source) {
            foreach ($this->getTypes() as $type) {
                $builder->add(
                    sprintf('%s_%s', $source->getId(), $type->getId()),
                    ChoiceType::class,
                    [
                        'required' => false,
                        'multiple' => false,
                        'placeholder' => 'marello.notificationmessage.system_configuration.fields.assigned_groups.empty',
                        'choice_loader' => new CallbackChoiceLoader(function () {
                            return $this->getGroupChoices();
                        })
                    ]
                );
            }
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['types'] = $this->getTypes();
        $view->vars['sources'] = $this->getSources();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    private function getTypes(): array
    {
        if (!$this->types) {
            $class = ExtendHelper::buildEnumValueClassName(
                NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ENUM_CODE
            );
            $this->types = $this->registry->getRepository($class)->findAll();
        }

        return $this->types;
    }

    private function getSources(): array
    {
        if (!$this->sources) {
            $class = ExtendHelper::buildEnumValueClassName(
                NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_ENUM_CODE
            );
            $this->sources = $this->registry->getRepository($class)->findAll();
        }

        return $this->sources;
    }

    private function getGroupChoices(): array
    {
        if (!$this->groupChoices) {
            $repository = $this->registry->getRepository(Group::class);
            /** @var Group[] $groups */
            $groups = $repository->findAll();

            $choices = [];
            foreach ($groups as $group) {
                $choices[$group->getName()] = $group->getId();
            }
            $this->groupChoices = $choices;
        }

        return $this->groupChoices;
    }
}
