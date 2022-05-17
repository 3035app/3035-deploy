<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MaybeRemoveTwigServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('twig')) {
            $container->removeDefinition('pagerfanta.twig_extension');
            $container->removeDefinition('pagerfanta.twig_runtime');
            $container->removeDefinition('pagerfanta.view.twig');
        }
    }
}
