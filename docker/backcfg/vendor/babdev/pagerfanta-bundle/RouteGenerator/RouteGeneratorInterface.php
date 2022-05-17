<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\RouteGenerator;

use Pagerfanta\RouteGenerator\RouteGeneratorInterface as PagerfantaRouteGeneratorInterface;

trigger_deprecation('babdev/pagerfanta-bundle', '2.5', 'The "%s" interface is deprecated and will be removed in 3.0. Use the "%s" interface instead.', RouteGeneratorInterface::class, PagerfantaRouteGeneratorInterface::class);

/**
 * @deprecated to be removed in BabDevPagerfantaBundle 3.0. Implement `Pagerfanta\RouteGenerator\RouteGeneratorInterface` instead.
 */
interface RouteGeneratorInterface extends PagerfantaRouteGeneratorInterface
{
}
