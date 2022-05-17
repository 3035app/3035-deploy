<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\RouteGenerator;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RouterAwareRouteGenerator implements RouteGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var array
     */
    private $options;

    /**
     * @throws InvalidArgumentException if missing required options
     */
    public function __construct(UrlGeneratorInterface $router, array $options)
    {
        // Check missing options
        if (!isset($options['routeName'])) {
            throw new InvalidArgumentException(sprintf('The "%s" class options requires a "routeName" parameter to be set.', self::class));
        }

        $this->router = $router;
        $this->options = $options;
    }

    public function __invoke(int $page): string
    {
        $pageParameter = $this->options['pageParameter'] ?? '[page]';
        $omitFirstPage = $this->options['omitFirstPage'] ?? false;
        $routeParams = $this->options['routeParams'] ?? [];
        $referenceType = $this->options['referenceType'] ?? UrlGeneratorInterface::ABSOLUTE_PATH;

        $pagePropertyPath = new PropertyPath($pageParameter);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($omitFirstPage) {
            $propertyAccessor->setValue($routeParams, $pagePropertyPath, $page > 1 ? $page : null);
        } else {
            $propertyAccessor->setValue($routeParams, $pagePropertyPath, $page);
        }

        return $this->router->generate($this->options['routeName'], $routeParams, $referenceType);
    }
}
