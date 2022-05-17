<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @deprecated to be removed in BabDevPagerfantaBundle 3.0.
 */
final class MaybeRemoveTranslatedViewsPass implements CompilerPassInterface
{
    /**
     * @var bool
     */
    private $internalUse;

    /**
     * @param bool $internalUse Flag indicating the pass was created by an internal bundle call (used to suppress runtime deprecations)
     */
    public function __construct(bool $internalUse = false)
    {
        $this->internalUse = $internalUse;
    }

    public function process(ContainerBuilder $container): void
    {
        if (false === $this->internalUse) {
            trigger_deprecation('babdev/pagerfanta-bundle', '2.2', 'The "%s" class is deprecated and will be removed in 3.0.', self::class);
        }

        if (!$container->has('translator')) {
            $container->removeDefinition('pagerfanta.view.default_translated');
            $container->removeDefinition('pagerfanta.view.semantic_ui_translated');
            $container->removeDefinition('pagerfanta.view.twitter_bootstrap_translated');
            $container->removeDefinition('pagerfanta.view.twitter_bootstrap3_translated');
            $container->removeDefinition('pagerfanta.view.twitter_bootstrap4_translated');
        }
    }
}
