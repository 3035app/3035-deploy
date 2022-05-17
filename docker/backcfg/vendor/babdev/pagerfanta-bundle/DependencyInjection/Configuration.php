<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public const EXCEPTION_STRATEGY_CUSTOM = 'custom';
    public const EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND = 'to_http_not_found';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('babdev_pagerfanta', 'array');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('babdev_pagerfanta', 'array');
        }

        $rootNode
            ->children()
                ->scalarNode('default_view')
                    ->info('The default Pagerfanta view to use in your application')
                    ->defaultValue('default')
                ->end()
                ->scalarNode('default_twig_template')
                    ->info('The default Twig template to use when using the Twig Pagerfanta view')
                    ->defaultValue('@BabDevPagerfanta/default.html.twig')
                ->end()
                ->arrayNode('exceptions_strategy')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('out_of_range_page')
                            ->info('The exception strategy if requesting a page outside the available pages in a paginated list; valid options are "custom" or "to_http_not_found"')
                            ->defaultValue(self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND)
                            ->validate()
                                ->ifTrue(static function ($v) { return !\in_array($v, [self::EXCEPTION_STRATEGY_CUSTOM, self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND]); })
                                ->then(static function ($v) {
                                    trigger_deprecation(
                                        'babdev/pagerfanta-bundle',
                                        '2.2',
                                        'Setting the "babdev_pagerfanta.exceptions_strategy.out_of_range_page" configuration option to "%s" is deprecated and will not be allowed in 3.0, set the option to one of the allowed values: [%s]',
                                        $v,
                                        implode(', ', [self::EXCEPTION_STRATEGY_CUSTOM, self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND])
                                    );

                                    return $v;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('not_valid_current_page')
                            ->info('The exception strategy if the current page is not an allowed value in a paginated list; valid options are "custom" or "to_http_not_found"')
                            ->defaultValue(self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND)
                            ->validate()
                                ->ifTrue(static function ($v) { return !\in_array($v, [self::EXCEPTION_STRATEGY_CUSTOM, self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND]); })
                                ->then(static function ($v) {
                                    trigger_deprecation(
                                        'babdev/pagerfanta-bundle',
                                        '2.2',
                                        'Setting the "babdev_pagerfanta.exceptions_strategy.not_valid_current_page" configuration option to "%s" is deprecated and will not be allowed in 3.0, set the option to one of the allowed values: [%s]',
                                        $v,
                                        implode(', ', [self::EXCEPTION_STRATEGY_CUSTOM, self::EXCEPTION_STRATEGY_TO_HTTP_NOT_FOUND])
                                    );

                                    return $v;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
