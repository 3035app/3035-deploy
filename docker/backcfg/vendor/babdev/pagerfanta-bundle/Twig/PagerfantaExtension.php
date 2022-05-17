<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\Twig;

use Pagerfanta\Twig\Extension\PagerfantaExtension as PagerfantaPagerfantaExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

trigger_deprecation('babdev/pagerfanta-bundle', '2.5', 'The "%s" class is deprecated and will be removed in 3.0. Use the "%s" class instead.', PagerfantaExtension::class, PagerfantaPagerfantaExtension::class);

/**
 * @deprecated to be removed in BabDevPagerfantaBundle 3.0. Use `Pagerfanta\Twig\Extension\PagerfantaExtension` instead.
 */
final class PagerfantaExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('pagerfanta', [PagerfantaRuntime::class, 'renderPagerfanta'], ['is_safe' => ['html'], 'deprecated' => true]),
            new TwigFunction('pagerfanta_page_url', [PagerfantaRuntime::class, 'getPageUrl'], ['deprecated' => true]),
        ];
    }
}
