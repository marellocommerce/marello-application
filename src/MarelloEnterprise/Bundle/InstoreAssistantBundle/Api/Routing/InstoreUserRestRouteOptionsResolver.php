<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Routing;

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
 * Adds the route "POST /api/instoreuser/authenticate".
 * The name of this route is "marelloenterprise_instoreassistant_rest_api_authenticate_instore_user".
 */
class InstoreUserRestRouteOptionsResolver implements RouteOptionsResolverInterface
{
    const INSTOREUSER_API_ROUTE_NAME = 'marelloenterprise_instoreassistant_rest_api_authenticate_instore_user';
    const INSTOREUSER_API_ROUT_ACTIION = 'authenticate';

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
            || self::INSTOREUSER_API_ROUT_ACTIION !== $route->getDefault('_action')
            || self::INSTOREUSER_API_ROUTE_NAME !== $routes->getName($route)
        ) {
            return;
        }
        file_put_contents(
            '/var/www/app/logs/api-request.log',
            __METHOD__ . " " . print_r($route->getPath(), true) . "\r\n",
            FILE_APPEND
        );
        $instoreUserModelEntityType = ValueNormalizerUtil::convertToEntityType(
            $this->valueNormalizer,
            InstoreUserApi::class,
            new RequestType([RequestType::REST]),
            false
        );

        if (!$instoreUserModelEntityType) {
            return;
        }

        // set "entity" attribute and remove it from the requirements
//        $route->setDefault(RestRouteOptionsResolver::ENTITY_ATTRIBUTE, $instoreUserModelEntityType);
//        $requirements = $route->getRequirements();
//        unset($requirements[RestRouteOptionsResolver::ENTITY_ATTRIBUTE]);
//        $route->setRequirements($requirements);
//        file_put_contents(
//            '/var/www/app/logs/api-request.log',
//            __METHOD__ . " " . print_r($route->getDefaults(), true) . "\r\n",
//            FILE_APPEND
//        );
    }
}
