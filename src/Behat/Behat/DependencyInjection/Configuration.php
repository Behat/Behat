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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('behat', 'array');

        $rootNode->
            arrayNode('paths')->
                scalarNode('base')->
                    defaultValue('BEHAT_WORK_PATH/features')->
                end()->
                scalarNode('features')->
                    defaultValue('%behat.paths.base%')->
                end()->
                scalarNode('steps')->
                    defaultValue('%behat.paths.base%/steps')->
                end()->
                scalarNode('steps-i18n')->
                    defaultValue('%behat.paths.base%/steps/i18n')->
                end()->
                scalarNode('support')->
                    defaultValue('%behat.paths.base%/support')->
                end()->
                scalarNode('bootstrap')->
                    defaultValue('%behat.paths.support%/bootstrap.php')->
                end()->
                scalarNode('environment')->
                    defaultValue('%behat.paths.support%/env.php')->
                end()->
                scalarNode('hooks')->
                    defaultValue('%behat.paths.support%/hooks.php')->
                end()->
            end()->
            arrayNode('filters')->
                scalarNode('name')->
                    defaultNull()->
                end()->
                scalarNode('tags')->
                    defaultNull()->
                end()->
            end()->
            arrayNode('formatter')->
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
                fixXmlConfig('parameter', 'parameters')->
                arrayNode('parameters')->
                    useAttributeAsKey(0)->
                    prototype('scalar')->end()->
                end()->
            end()->
            arrayNode('classes')->
                scalarNode('environment')->
                    defaultValue('Behat\Behat\Environment\Environment')->
                end()->
                scalarNode('formatter')->
                    defaultNull()->
                end()->
            end();

        return $treeBuilder->buildTree();
    }
}
