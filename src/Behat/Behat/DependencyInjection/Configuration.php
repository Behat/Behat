<?php

namespace Behat\Behat\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder,
    Symfony\Component\Config\Definition\Builder\TreeBuilder;

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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return Symfony\Component\Config\Definition\NodeInterface
     */
    public function getConfigTree()
    {
        $tree = new TreeBuilder();

        $tree->root('behat')->
            children()->
                arrayNode('paths')->
                    children()->
                        scalarNode('features')->
                            defaultValue('%%BEHAT_BASE_PATH%%')->
                        end()->
                        scalarNode('support')->
                            defaultValue('%behat.paths.features%/support')->
                        end()->
                        arrayNode('steps')->
                            prototype('scalar')->end()->
                            defaultValue(array('%behat.paths.features%/steps'))->
                        end()->
                        arrayNode('steps_i18n')->
                            prototype('scalar')->end()->
                            defaultValue(array('%behat.paths.features%/steps/i18n'))->
                        end()->
                        arrayNode('bootstrap')->
                            prototype('scalar')->end()->
                            defaultValue(array('%behat.paths.support%/bootstrap.php'))->
                        end()->
                        arrayNode('environment')->
                            prototype('scalar')->end()->
                            defaultValue(array('%behat.paths.support%/env.php'))->
                        end()->
                        arrayNode('hooks')->
                            prototype('scalar')->end()->
                            defaultValue(array('%behat.paths.support%/hooks.php'))->
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
                        booleanNode('decorated')->
                            defaultNull()->
                        end()->
                        booleanNode('verbose')->
                            defaultFalse()->
                        end()->
                        booleanNode('time')->
                            defaultTrue()->
                        end()->
                        scalarNode('language')->
                            defaultValue('en')->
                        end()->
                        scalarNode('output_path')->
                            defaultNull()->
                        end()->
                        booleanNode('multiline_arguments')->
                            defaultTrue()->
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
                        scalarNode('rerun')->
                            defaultNull()->
                        end()->
                        booleanNode('strict')->
                            defaultNull()->
                        end()->
                    end()->
                end()->
            end()->
            children()->
                arrayNode('environment')->
                    fixXmlConfig('parameter')->
                    children()->
                        scalarNode('class')->
                            defaultValue('Behat\Behat\Environment\Environment')->
                        end()->
                        arrayNode('parameters')->
                            useAttributeAsKey(0)->
                            prototype('variable')->end()->
                        end()->
                    end()->
                end()->
            end();

        return $tree->buildTree();
    }
}
