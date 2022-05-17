<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle;

use BabDev\PagerfantaBundle\DependencyInjection\BabDevPagerfantaExtension;
use BabDev\PagerfantaBundle\DependencyInjection\CompilerPass\AddPagerfantasPass;
use BabDev\PagerfantaBundle\DependencyInjection\CompilerPass\MaybeRemoveTranslatedViewsPass;
use BabDev\PagerfantaBundle\DependencyInjection\CompilerPass\MaybeRemoveTwigServicesPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class BabDevPagerfantaBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // MaybeRemoveTranslatedViewsPass must be run before the AddPagerfantasPass
        // MaybeRemoveTwigServicesPass must be run before the TwigEnvironmentPass from TwigBundle and AddPagerfantasPass
        $container->addCompilerPass(new MaybeRemoveTranslatedViewsPass(true));
        $container->addCompilerPass(new AddPagerfantasPass());
        $container->addCompilerPass(new MaybeRemoveTwigServicesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new BabDevPagerfantaExtension();
        }

        return $this->extension ?: null;
    }
}
