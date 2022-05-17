<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\View;

use BabDev\PagerfantaBundle\RouteGenerator\RouteGeneratorDecorator;
use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\PagerfantaInterface;
use Pagerfanta\Twig\View\TwigView as PagerfantaTwigView;
use Pagerfanta\View\View;
use Twig\Environment;

/**
 * @deprecated to be removed in BabDevPagerfantaBundle 3.0. Use `Pagerfanta\Twig\View\TwigView` instead.
 */
final class TwigView extends View
{
    public const DEFAULT_TEMPLATE = '@BabDevPagerfanta/default.html.twig';

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string|null
     */
    private $defaultTemplate;

    /**
     * @var string
     */
    private $template;

    public function __construct(Environment $twig, ?string $defaultTemplate = null)
    {
        $this->twig = $twig;
        $this->defaultTemplate = $defaultTemplate;
    }

    public function getName()
    {
        return 'twig';
    }

    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = [])
    {
        trigger_deprecation('babdev/pagerfanta-bundle', '2.5', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class instead.', self::class, PagerfantaTwigView::class);

        $this->initializePagerfanta($pagerfanta);
        $this->initializeOptions($options);

        $this->calculateStartAndEndPage();

        return $this->twig->load($this->template)->renderBlock(
            'pager_widget',
            [
                'pagerfanta' => $pagerfanta,
                'route_generator' => $this->decorateRouteGenerator($routeGenerator),
                'options' => $options,
                'start_page' => $this->startPage,
                'end_page' => $this->endPage,
                'current_page' => $this->currentPage,
                'nb_pages' => $this->nbPages,
            ]
        );
    }

    private function decorateRouteGenerator($routeGenerator): RouteGeneratorDecorator
    {
        if (!\is_callable($routeGenerator)) {
            throw new InvalidArgumentException(sprintf('The route generator for "%s" must be a callable, a "%s" was given.', self::class, \gettype($routeGenerator)));
        }

        return new RouteGeneratorDecorator($routeGenerator);
    }

    protected function initializeOptions(array $options): void
    {
        if (isset($options['template'])) {
            $this->template = $options['template'];
        } elseif (null !== $this->defaultTemplate) {
            $this->template = $this->defaultTemplate;
        } else {
            $this->template = self::DEFAULT_TEMPLATE;
        }

        parent::initializeOptions($options);
    }
}
