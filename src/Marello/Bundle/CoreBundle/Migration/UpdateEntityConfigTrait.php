<?php

namespace Marello\Bundle\CoreBundle\Migration;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

trait UpdateEntityConfigTrait
{
    use ContainerAwareTrait;

    /**
     * @param array $fields
     * @param $entityClassName
     * @return void
     */
    private function updateEntityConfigFields(array $fields, $entityClassName): void
    {
        $configManager = $this->getConfigManager();
        if ($configManager) {
            $entityManager = $configManager->getEntityManager();
            $configHelper = $this->container->get('oro_entity_config.config.config_helper');
            foreach ($fields as $field => $options) {
                $fieldConfigModel = $configManager->getConfigFieldModel($entityClassName, $field);
                $configHelper->updateFieldConfigs($fieldConfigModel, $options);
                $entityManager->persist($fieldConfigModel);
            }

            $entityManager->flush();
        }
    }

    /**
     * @return object|null
     */
    private function getConfigManager(): ?object
    {
        return $this->container->get('oro_entity_config.config_manager');
    }
}
