<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer\Configuration;

use Behat\Testwork\ServiceContainer\Extension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * Builds configuration tree using provided lists of core and custom extensions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ConfigurationTree
{
    /**
     * Generates the configuration tree.
     *
     * @param Extension[] $extensions
     *
     * @return NodeInterface
     */
    public function getConfigTree(array $extensions)
    {
        if (method_exists('Symfony\Component\Config\Definition\Builder\TreeBuilder', 'getRootNode')) {
            $treeBuilder = new TreeBuilder('testwork');
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $treeBuilder = new TreeBuilder();
            /** @var ArrayNodeDefinition $rootNode */
            /** @scrutinizer ignore-call */
            $rootNode = $treeBuilder->root('testwork');
        }

        foreach ($extensions as $extension) {
            $extension->configure($rootNode->children()->arrayNode($extension->getConfigKey()));
        }

        return $treeBuilder->buildTree();
    }
}
