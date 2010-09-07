<?php

namespace Everzet\Behat\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat dependency injection service container.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ServiceContainer extends Container
{
    protected $shared = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(new ParameterBag($this->getDefaultParameters()));
    }

    /**
     * Gets the 'event_dispatcher' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %event_dispatcher.class% instance.
     */
    protected function getEventDispatcherService()
    {
        if (isset($this->shared['event_dispatcher'])) return $this->shared['event_dispatcher'];

        $class = $this->getParameter('event_dispatcher.class');
        $instance = new $class($this);
        $this->shared['event_dispatcher'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'parser' service.
     *
     * @return Object A %parser.class% instance.
     */
    protected function getParserService()
    {
        $class = $this->getParameter('parser.class');
        $instance = new $class($this->getI18nService());

        return $instance;
    }

    /**
     * Gets the 'i18n' service.
     *
     * @return Object A %i18n.class% instance.
     */
    protected function getI18nService()
    {
        $class = $this->getParameter('i18n.class');
        $instance = new $class($this->getParameter('i18n.path'));

        return $instance;
    }

    /**
     * Gets the 'environment' service.
     *
     * @return Object A %environment.class% instance.
     */
    protected function getEnvironmentService()
    {
        $class = $this->getParameter('environment.class');
        $instance = new $class($this->getParameter('environment.file'));

        return $instance;
    }

    /**
     * Gets the 'formatter' service.
     *
     * @return Object A %formatter.class% instance.
     */
    protected function getFormatterService()
    {
        $class = $this->getParameter('formatter.class');
        $class = strtr($class, array('%formatter.name%' => $this->getParameter('formatter.name')));
        $instance = new $class($this);

        return $instance;
    }

    /**
     * Gets the 'features_loader' service.
     *
     * @return Object A %features_loader.class% instance.
     */
    protected function getFeaturesLoaderService()
    {
        $class = $this->getParameter('features_loader.class');
        $instance = new $class($this->getParameter('features.path'), $this);

        return $instance;
    }

    /**
     * Gets the 'steps_loader' service.
     *
     * @return Object A %steps_loader.class% instance.
     */
    protected function getStepsLoaderService()
    {
        if (isset($this->shared['steps_loader'])) return $this->shared['steps_loader'];

        $class = $this->getParameter('steps_loader.class');
        $instance = new $class($this->getParameter('steps.path'));
        $this->shared['steps_loader'] = $instance;

        return $instance;
    }

    /**
     * Returns service ids for a given tag.
     *
     * @param string $name The tag name
     *
     * @return array An array of tags
     */
    public function findTaggedServiceIds($name)
    {
        static $tags = array(
            'events_listener' => array('formatter'),
        );

        return isset($tags[$name]) ? $tags[$name] : array();
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'parser.class'              => 'Everzet\\Gherkin\\Parser',
            'i18n.class'                => 'Everzet\\Gherkin\\I18n',
            'environment.class'         => 'Everzet\\Behat\\Environment\\WorldEnvironment',
            'features_loader.class'     => 'Everzet\\Behat\\Loader\\FeaturesLoader',
            'steps_loader.class'        => 'Everzet\\Behat\\Loader\\StepsLoader',
            'formatter.class'           => 'Everzet\\Behat\\Formatter\\%formatter.name%Formatter',
            'event_dispatcher.class'    => 'Everzet\\Behat\\EventDispatcher\\EventDispatcher',
        );
    }
}
