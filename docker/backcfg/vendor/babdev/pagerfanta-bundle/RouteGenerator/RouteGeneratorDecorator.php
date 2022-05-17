<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\RouteGenerator;

use Pagerfanta\RouteGenerator\RouteGeneratorDecorator as PagerfantaRouteGeneratorDecorator;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

trigger_deprecation('babdev/pagerfanta-bundle', '2.5', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class instead.', RouteGeneratorDecorator::class, PagerfantaRouteGeneratorDecorator::class);

/**
 * @deprecated to be removed in BabDevPagerfantaBundle 3.0. Use `Pagerfanta\RouteGenerator\RouteGeneratorDecorator` instead.
 */
final class RouteGeneratorDecorator implements RouteGeneratorInterface
{
    /**
     * @var callable
     */
    private $decorated;

    public function __construct(callable $decorated)
    {
        $this->decorated = $decorated;
    }

    public function __invoke(int $page): string
    {
        return $this->route($page);
    }

    public function route(int $page): string
    {
        $decorated = $this->decorated;

        return $decorated($page);
    }
}
