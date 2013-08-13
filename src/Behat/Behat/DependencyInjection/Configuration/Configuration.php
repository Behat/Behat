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
        $defaultAutoload = array('' => '%paths.base%/features/bootstrap');
        $defaultSuiteParameters = array(
            'type'     => 'gherkin',
            'paths'    => array('%paths.base%/features'),
            'contexts' => array('FeatureContext'),
        );
        $defaultSuites = array(
            'default' => $defaultSuiteParameters
        );
        $defaultFormatters = array(
            'pretty' => array('enabled' => true),
        );

        return $tree->root('behat')
            ->children()

                ->arrayNode('autoload')
                    ->treatFalseLike(array())
                    ->defaultValue($defaultAutoload)
                    ->treatTrueLike($defaultAutoload)
                    ->treatNullLike($defaultAutoload)
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
                    ->defaultValue($defaultSuites)
                    ->treatTrueLike($defaultSuites)
                    ->treatNullLike($defaultSuites)
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifTrue(function($suite) {
                                return (!isset($suite['type']) || 'basic' === $suite['type']);
                            })
                            ->then(function($suite) use($defaultSuiteParameters) {
                                if (isset($suite['path'])) {
                                    $suite['paths'] = array($suite['path']);
                                    unset($suite['path']);
                                }
                                if (isset($suite['context'])) {
                                    $suite['contexts'] = array($suite['context']);
                                    unset($suite['context']);
                                }

                                return array_replace_recursive($defaultSuiteParameters, $suite);
                            })
                        ->end()
                        ->useAttributeAsKey('name')
                        ->prototype('variable')->end()
                    ->end()
                ->end()

                ->arrayNode('formatters')
                    ->defaultValue($defaultFormatters)
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
