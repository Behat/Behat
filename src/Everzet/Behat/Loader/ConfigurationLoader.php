<?php

namespace Everzet\Behat\Loader;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Configuration Loader.
 * Loads configuration from external file(s).
 * 
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConfigurationLoader implements LoaderInterface
{
    protected $container;

    /**
     * Initialize loader.
     * 
     * @param   ContainerBuilder    $container 
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Load configuration from specified path(s).
     *
     * @param   string|array    $paths  features path(s)
     * 
     * @return  FeaturesRunner          features runner instance
     */
    public function load($paths)
    {
        foreach ((array) $paths as $path) {
            $path = strtr($path, array(
                '%%behat.work.path%%'   => $this->container->getParameter('behat.work.path')
              , '%%behat.lib.path%%'    => $this->container->getParameter('behat.lib.path')
            ));

            if (false !== mb_stripos($path, '.xml')) {
                $loader = new XmlFileLoader($this->container);
            } elseif (false !== mb_stripos($path, '.yml') || false !== mb_stripos($path, '.yaml')) {
                $loader = new YamlFileLoader($this->container);
            } elseif (false !== mb_stripos($path, '.php')) {
                $loader = new PhpFileLoader($this->container);
            }

            $loader->import($path);
        }
    }

    /**
     * Prepare container parameterers to work.
     */
    public function prepareContainerParameters()
    {
        // Find proper features path
        $featuresPath   = $this->container->getParameter('behat.features.path');
        if (is_dir($featuresPath . '/features')) {
            $featuresPath = $featuresPath . '/features';
            $this->container->setParameter('behat.features.path', $featuresPath);
        } elseif (is_file($featuresPath)) {
            $this->container->setParameter('behat.features.path', dirname($featuresPath));
        }
        $this->container->setParameter('behat.features.files', $featuresPath);

        // Uppercasing formatter name
        $this->container->setParameter('behat.formatter.name', 
            ucfirst($this->container->getParameter('behat.formatter.name'))
        );

        foreach ($this->container->getParameterBag()->all() as $key => $value) {
            $compiled   = array();
            $container  = $this->container;
            foreach ((array) $value as $i => $item) {
                $compiled[$i] = preg_replace_callback('/%%([^%]+)%%/', 
                    function($matches) use($container) {
                        return $container->getParameter($matches[1]);
                    }
                  , $item
                );
            }
            if (!isset($compiled[0])) {
                $compiled[0] = $value;
            }
            $this->container->setParameter($key, is_array($value) ? $compiled : $compiled[0]);
        }
    }
}
