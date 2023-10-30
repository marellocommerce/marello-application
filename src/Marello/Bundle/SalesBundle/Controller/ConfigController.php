<?php

namespace Marello\Bundle\SalesBundle\Controller;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Provider\SalesChannelConfigurationFormProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Form\Handler\ConfigHandler;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SyncBundle\Content\DataUpdateTopicSender;
use Oro\Bundle\SyncBundle\Content\TagGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConfigController extends AbstractController
{
    /**
     * @Route(
     *      "/saleschannel/{id}/{activeGroup}/{activeSubGroup}",
     *      name="marello_sales_config_saleschannel",
     *      requirements={"id"="\d+"},
     *      defaults={"activeGroup" = null, "activeSubGroup" = null}
     * )
     * @Template()
     * @AclAncestor("marello_sales_saleschannel_update")
     * @param Request $request
     * @param SalesChannel $entity
     * @param mixed $activeGroup
     * @param mixed $activeSubGroup
     * @return array
     */
    public function salesChannelAction(
        Request $request,
        SalesChannel $entity,
        $activeGroup = null,
        $activeSubGroup = null
    ) {
        /** @var SalesChannelConfigurationFormProvider $provider */
        $provider = $this->container->get(SalesChannelConfigurationFormProvider::class);
        /** @var ConfigManager $manager */
        $manager = $this->container->get(ConfigManager::class);
        $prevScopeId = $manager->getScopeId();
        $manager->setScopeIdFromEntity($entity);

        list($activeGroup, $activeSubGroup) = $provider->chooseActiveGroups($activeGroup, $activeSubGroup);

        $jsTree = $provider->getJsTree();
        $form = false;

        if ($activeSubGroup !== null) {
            $form = $provider->getForm($activeSubGroup, $manager);

            if ($this->container->get(ConfigHandler::class)
                ->setConfigManager($manager)
                ->process($form, $request)
            ) {
                $request->getSession()->getFlashBag()->add(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('oro.config.controller.config.saved.message')
                );

                // outdate content tags, it's only special case for generation that are not covered by NavigationBundle
                $taggableData = ['name' => 'saleschannel_configuration', 'params' => [$activeGroup, $activeSubGroup]];
                $tagGenerator = $this->container->get(TagGeneratorInterface::class);
                $dataUpdateTopicSender = $this->container->get(DataUpdateTopicSender::class);

                $dataUpdateTopicSender->send($tagGenerator->generate($taggableData));

                // recreate form to drop values for fields with use_parent_scope_value
                $form = $provider->getForm($activeSubGroup, $manager);
                $form->setData($manager->getSettingsByForm($form));
            }
        }
        $manager->setScopeId($prevScopeId);

        return [
            'entity'         => $entity,
            'data'           => $jsTree,
            'form'           => $form ? $form->createView() : null,
            'activeGroup'    => $activeGroup,
            'activeSubGroup' => $activeSubGroup,
            'scopeEntity'    => $entity,
            'scopeEntityClass' => SalesChannel::class,
            'scopeEntityId'  => $entity->getId()
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                SalesChannelConfigurationFormProvider::class,
                ConfigHandler::class,
                TranslatorInterface::class,
                TagGeneratorInterface::class,
                DataUpdateTopicSender::class,
                ConfigManager::class,
            ]
        );
    }
}
