<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Represents Testwork extension mechanism.
 *
 * Extensions are the core entities in Testwork. Almost all framework functionality in Testwork and its different
 * implementations is provided through extensions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Extension extends CompilerPassInterface
{
    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey();

    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     */
    public function initialize(ExtensionManager $extensionManager);

    /**
     * Setups configuration for the extension.
     *
     * NOTE: If your extension implements this method, your composer.json should declare
     * a direct dependency on the version(s) of symfony/config that you support.
     */
    public function configure(ArrayNodeDefinition $builder);

    /**
     * Loads extension services into temporary container.
     *
     * NOTE: If your extension implements this method, your composer.json should declare
     * a direct dependency on the version(s) of symfony/dependency-injection that you support.
     *
     * @param array<string, mixed> $config
     */
    public function load(ContainerBuilder $container, array $config);

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     *  NOTE: If your extension implements this method, your composer.json should declare
     *  a direct dependency on the version(s) of symfony/dependency-injection that you support.
     *
     * @return void
     */
    public function process(ContainerBuilder $container);
}
