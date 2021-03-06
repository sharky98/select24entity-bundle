<?php

namespace Brunops\Select24EntityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {

  /**
   * {@inheritDoc}
   */
  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    $rootNode = $treeBuilder->root('brunops_select24entity');

    $rootNode
            ->children()
            ->scalarNode('minimum_input_length')->defaultValue(1)->end()
            ->scalarNode('page_limit')->defaultValue(10)->end()
            ->scalarNode('allow_clear')->defaultFalse()->end()
            ->scalarNode('delay')->defaultValue(250)->end()
            ->scalarNode('language')->defaultValue('en')->end()
            ->scalarNode('cache')->defaultTrue()->end()
            ->scalarNode('cache_timeout')->defaultValue(60000)->end()
            ->end();

    return $treeBuilder;
  }

}
