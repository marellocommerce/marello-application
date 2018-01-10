<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\ApiDoc;

use Symfony\Component\Routing\Route;

use Oro\Component\Routing\Resolver\RouteCollectionAccessor;
use Oro\Component\Routing\Resolver\RouteOptionsResolverInterface;
use Oro\Bundle\ApiBundle\ApiDoc\RestRouteOptionsResolver;
use Oro\Bundle\ApiBundle\Request\ApiActions;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ApiBundle\Util\ValueNormalizerUtil;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;

/**
 * Removes auto-generated API route "GET /api/instoreuserapi/{id}".
 */
class InstoreUserRestRouteOptionsResolver implements RouteOptionsResolverInterface
{
    /** @var ValueNormalizer */
    private $valueNormalizer;

    /**
     * @param ValueNormalizer $valueNormalizer
     */
    public function __construct(ValueNormalizer $valueNormalizer)
    {
        $this->valueNormalizer = $valueNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Route $route, RouteCollectionAccessor $routes)
    {
        if (RestRouteOptionsResolver::ROUTE_GROUP !== $route->getOption('group')
            || ApiActions::GET !== $route->getDefault('_action')
            || $route->getDefault(RestRouteOptionsResolver::ENTITY_ATTRIBUTE)
        ) {
            return;
        }

        $userProfileEntityType = ValueNormalizerUtil::convertToEntityType(
            $this->valueNormalizer,
            InstoreUserApi::class,
            new RequestType([RequestType::REST]),
            false
        );

        if (!$userProfileEntityType) {
            return;
        }

        $userProfileGetRoutePath = str_replace(
            RestRouteOptionsResolver::ENTITY_PLACEHOLDER,
            $userProfileEntityType,
            $route->getPath()
        );
        $userProfileGetRoute = $routes->getByPath($userProfileGetRoutePath, $route->getMethods());

        file_put_contents(
            '/var/www/app/logs/api-debug.log',
            print_r($userProfileGetRoutePath, true) . "\r\n",
            FILE_APPEND
        );

        file_put_contents(
            '/var/www/app/logs/api-debug.log',
            print_r($route->getMethods(), true) . "\r\n",
            FILE_APPEND
        );
        if (null !== $userProfileGetRoute) {
            $routes->remove($routes->getName($userProfileGetRoute));
        }
    }
}
