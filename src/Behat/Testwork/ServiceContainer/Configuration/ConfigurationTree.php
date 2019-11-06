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
        $rootName = 'testwork';
        if ($this->isOlderTreeBuilder()) {
            $tree = new TreeBuilder();
            $root = $tree->root($rootName);
        } else {
            $tree = new TreeBuilder($rootName);
            $root = $tree->getRootNode();
        }
        foreach ($extensions as $extension) {
            $extension->configure($root->children()->arrayNode($extension->getConfigKey()));
        }
        
        return $tree->buildTree();
    }
    
    private function isOlderTreeBuilder()
    {
        return method_exists('\\Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder',
            'root');
    }
}
