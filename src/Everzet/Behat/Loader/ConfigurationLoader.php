<?php

namespace Everzet\Behat\Loader;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ConfigurationLoader 
 * Loads configuration from external file(s)
 * 
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConfigurationLoader implements LoaderInterface
{
    protected $container;

    /**
     * Inits loader
     * 
     * @param   ContainerBuilder    $container 
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Loads configuration from specified path(s)
     *
     * @param   string|array    $paths  features path(s)
     * 
     * @return  FeaturesRunner          features runner instance
     */
    public function load($paths)
    {
        foreach ((array) $paths as $path) {
            $path = strtr($path, array(
                '%%dir.work%%'  => $this->container->getParameter('dir.work')
              , '%%dir.lib%%'   => $this->container->getParameter('dir.lib')
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
     * Prepares container parameterers to work 
     */
    public function prepareContainerParameters()
    {
        // Find proper features path
        $featuresPath   = $this->container->getParameter('features.path');
        if (is_dir($featuresPath . '/features')) {
            $featuresPath = $featuresPath . '/features';
            $this->container->setParameter('features.path', $featuresPath);
        } elseif (is_file($featuresPath)) {
            $this->container->setParameter('features.path', dirname($featuresPath));
        }
        $this->container->setParameter('features.files', $featuresPath);

        // Uppercasing formatter name
        $this->container->setParameter('formatter.name', 
            ucfirst($this->container->getParameter('formatter.name'))
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

