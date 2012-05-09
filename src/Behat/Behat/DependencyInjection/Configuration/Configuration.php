<?php

namespace Behat\Behat\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeBuilder,
    Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\NodeInterface,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class contains the configuration information for the Behat
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return NodeInterface
     */
    public function getConfigTree()
    {
        $tree = new TreeBuilder();
        $this->appendConfigChildrens($tree);

        return $tree->buildTree();
    }

    /**
     * Appends config childrens to configuration tree.
     *
     * @param TreeBuilder $tree   tree builder
     *
     * @return ArrayNodeDefinition
     */
    protected function appendConfigChildrens(TreeBuilder $tree)
    {
        return $tree->root('behat')->
            children()->
                arrayNode('paths')->
                    children()->
                        scalarNode('features')->
                            defaultValue('%behat.paths.base%/features')->
                        end()->
                        scalarNode('bootstrap')->
                            defaultValue('%behat.paths.features%/bootstrap')->
                        end()->
                    end()->
                end()->
            end()->
            children()->
                arrayNode('filters')->
                    children()->
                        scalarNode('name')->defaultNull()->end()->
                        scalarNode('tags')->defaultNull()->end()->
                    end()->
                end()->
            end()->
            children()->
                arrayNode('formatter')->
                    fixXmlConfig('parameter')->
                    children()->
                        scalarNode('name')->
                            defaultValue('pretty')->
                        end()->
                        arrayNode('classes')->
                            useAttributeAsKey(0)->
                            prototype('variable')->end()->
                        end()->
                        arrayNode('parameters')->
                            useAttributeAsKey(0)->
                            prototype('variable')->end()->
                        end()->
                    end()->
                end()->
            end()->
            children()->
                arrayNode('options')->
                    fixXmlConfig('option')->
                    children()->
                        scalarNode('cache')->
                            defaultNull()->
                        end()->
                        booleanNode('strict')->
                            defaultNull()->
                        end()->
                        booleanNode('dry_run')->
                            defaultNull()->
                        end()->
                        scalarNode('rerun')->
                            defaultNull()->
                        end()->
                        scalarNode('append_snippets')->
                            defaultNull()->
                        end()->
                    end()->
                end()->
            end()->
            children()->
                arrayNode('context')->
                    fixXmlConfig('parameter')->
                    children()->
                        scalarNode('class')->end()->
                        arrayNode('parameters')->
                            useAttributeAsKey(0)->
                            prototype('variable')->end()->
                        end()->
                    end()->
                end()->
            end()->
            fixXmlConfig('extension')->
            children()->
                arrayNode('extensions')->
                    useAttributeAsKey(0)->
                    prototype('variable')->end()->
                end()->
            end()
        ;
    }
}
