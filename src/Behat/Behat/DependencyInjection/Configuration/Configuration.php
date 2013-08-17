<?php

namespace Behat\Behat\DependencyInjection\Configuration;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Extension\ExtensionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
     * @param ExtensionInterface[] $extensions
     *
     * @return NodeInterface
     */
    public function getConfigTree(array $extensions)
    {
        $tree = new TreeBuilder();
        $root = $this->appendConfigChildren($tree);

        $extensionsNode = $root
            ->children()
                ->arrayNode('extensions')
                    ->addDefaultsIfNotSet()
                        ->children();

        foreach ($extensions as $extension) {
            $extensionNode = $extensionsNode->arrayNode($extension->getName());
            $extension->getConfig($extensionNode);
        }

        return $tree->buildTree();
    }

    /**
     * Appends config children to configuration tree.
     *
     * @param TreeBuilder $tree tree builder
     *
     * @return ArrayNodeDefinition
     */
    protected function appendConfigChildren(TreeBuilder $tree)
    {
        return $tree->root('behat')
            ->children()

                ->arrayNode('autoload')
                    ->defaultValue(array('' => '%paths.base%/features/bootstrap'))
                    ->treatTrueLike(array('' => '%paths.base%/features/bootstrap'))
                    ->treatFalseLike(array())
                    ->treatNullLike(array())
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function($path) {
                            return array('' => $path);
                        })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()

                ->arrayNode('suites')
                    ->treatFalseLike(array())
                    ->treatNullLike(array())
                    ->defaultValue(array('default' => array(
                        'type'       => 'gherkin',
                        'settings'   => array(),
                        'parameters' => array()
                    )))
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->beforeNormalization()
                            ->ifTrue(function($suite) {
                                return count($suite);
                            })
                            ->then(function($suite) {
                                $suite['settings'] = isset($suite['settings'])
                                    ? $suite['settings']
                                    : array();

                                foreach ($suite as $key => $val) {
                                    if (!in_array($key, array('type', 'settings', 'parameters'))) {
                                        $suite['settings'][$key] = $val;
                                        unset($suite[$key]);
                                    }
                                }

                                return $suite;
                            })
                        ->end()
                        ->children()
                            ->scalarNode('type')
                                ->defaultValue('gherkin')
                            ->end()
                            ->arrayNode('settings')
                                ->defaultValue(array())
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                            ->arrayNode('parameters')
                                ->defaultValue(array())
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('formatters')
                    ->defaultValue(array('pretty' => array('enabled' => true)))
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->useAttributeAsKey('name')
                        ->treatFalseLike(array('enabled' => false))
                        ->treatTrueLike(array('enabled' => true))
                        ->treatNullLike(array('enabled' => true))
                        ->beforeNormalization()
                            ->ifTrue(function($a) {
                                return is_array($a) && !isset($a['enabled']);
                            })
                            ->then(function($a) { return
                                array_merge($a, array('enabled' => true));
                            })
                        ->end()
                        ->prototype('variable')->end()
                    ->end()
                ->end()

                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('error_reporting')
                            ->defaultValue(E_ALL)
                        ->end()
                        ->scalarNode('cache_path')
                            ->defaultValue(
                                is_writable(sys_get_temp_dir())
                                    ? sys_get_temp_dir().DIRECTORY_SEPARATOR.'behat_cache'
                                    : null
                            )
                        ->end()
                        ->booleanNode('strict')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('dry_run')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('stop_on_failure')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('append_snippets')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()

            ->end()
        ;
    }
}
