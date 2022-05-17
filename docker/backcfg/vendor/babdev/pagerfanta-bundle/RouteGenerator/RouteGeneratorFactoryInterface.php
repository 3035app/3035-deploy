<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\RouteGenerator;

use Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface as PagerfantaRouteGeneratorFactoryInterface;

trigger_deprecation('babdev/pagerfanta-bundle', '2.5', 'The "%s" interface is deprecated and will be removed in 3.0. Use the "%s" interface instead.', RouteGeneratorFactoryInterface::class, PagerfantaRouteGeneratorFactoryInterface::class);

/**
 * @deprecated to be removed in BabDevPagerfantaBundle 3.0. Implement `Pagerfanta\RouteGenerator\RouteGeneratorFactoryInterface` instead.
 */
interface RouteGeneratorFactoryInterface extends PagerfantaRouteGeneratorFactoryInterface
{
}
