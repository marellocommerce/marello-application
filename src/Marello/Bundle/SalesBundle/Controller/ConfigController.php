<?php

namespace Marello\Bundle\SalesBundle\Controller;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        $provider = $this->get('marello_sales.config_form_provider.saleschannel');
        /** @var ConfigManager $manager */
        $manager = $this->get('oro_config.saleschannel');
        $prevScopeId = $manager->getScopeId();
        $manager->setScopeIdFromEntity($entity);

        list($activeGroup, $activeSubGroup) = $provider->chooseActiveGroups($activeGroup, $activeSubGroup);

        $jsTree = $provider->getJsTree();
        $form = false;

        if ($activeSubGroup !== null) {
            $form = $provider->getForm($activeSubGroup);

            if ($this->get('oro_config.form.handler.config')
                ->setConfigManager($manager)
                ->process($form, $request)
            ) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('oro.config.controller.config.saved.message')
                );

                // outdate content tags, it's only special case for generation that are not covered by NavigationBundle
                $taggableData = ['name' => 'saleschannel_configuration', 'params' => [$activeGroup, $activeSubGroup]];
                $tagGenerator = $this->get('oro_sync.content.tag_generator');
                $dataUpdateTopicSender = $this->get('oro_sync.content.data_update_topic_sender');

                $dataUpdateTopicSender->send($tagGenerator->generate($taggableData));

                // recreate form to drop values for fields with use_parent_scope_value
                $form = $provider->getForm($activeSubGroup);
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
}
