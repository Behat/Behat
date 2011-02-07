<?php

namespace Behat\Behat\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\DependencyInjection\ContainerBuilder;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat service extension.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatExtension extends Extension
{
    /**
     * Load service configuration.
     *
     * @param   array               $config     configuration parameters
     * @param   ContainerBuilder    $container  service container
     */
    public function configLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('behat.paths.lib')) {
            $this->loadDefaults($container);
        }

        if (isset($config['paths'])) {
            foreach ($config['paths'] as $key => $value) {
                $parameterName = "behat.paths.$key";

                if (!$container->hasParameter($parameterName)) {
                    throw new \InvalidArgumentException("Path parameter $key doesn't exists");
                }

                $container->setParameter($parameterName, $value);
            }
        }

        if (isset($config['format'])) {
            foreach ($config['format'] as $key => $value) {
                $parameterName = "behat.formatter.$key";

                if (!$container->hasParameter($parameterName)) {
                throw new \InvalidArgumentException("Formatter parameter $key doesn't exists");
                }

                $container->setParameter($parameterName, $value);
            }
        }

        if (isset($config['filters'])) {
            foreach ($config['filters'] as $key => $value) {
                $parameterName = "gherkin.filter.$key";

                if (!$container->hasParameter($parameterName)) {
                    throw new \InvalidArgumentException("$key filter doesn't exists");
                }

                $container->setParameter($parameterName, $value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__ . '/config/schema';
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return 'http://behat.com/schema/dic/behat';
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'behat';
    }

    /**
     * {@inheritdoc}
     */
    protected function loadDefaults($container)
    {
        $loader = new XmlFileLoader($container, __DIR__ . '/config');
        $loader->load('behat.xml');

        $behatClassLoaderReflection = new \ReflectionClass('Behat\Behat\Console\BehatApplication');
        $gherkinParserReflection    = new \ReflectionClass('Behat\Gherkin\Parser');

        $behatLibPath   = realpath(dirname($behatClassLoaderReflection->getFilename()) . '/../../../../');
        $gherkinLibPath = realpath(dirname($gherkinParserReflection->getFilename()) . '/../../../');

        $container->setParameter('gherkin.paths.lib', $gherkinLibPath);
        $container->setParameter('behat.paths.lib', $behatLibPath);
    }
}
