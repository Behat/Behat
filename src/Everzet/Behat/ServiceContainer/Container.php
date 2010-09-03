<?php

namespace Everzet\Behat\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container as BaseContainer;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Container
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class Container extends BaseContainer
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
     * @return Object A %world.class% instance.
     */
    protected function getEnvironmentService()
    {
        $class = $this->getParameter('environment.class');
        $instance = new $class($this->getParameter('environment.file'));

        return $instance;
    }

    /**
     * Gets the 'logger' service.
     *
     * @return Object A %logger.class% instance
     */
    protected function getLoggerService()
    {
        $class = $this->getParameter('logger.class');
        $instance = new $class($this);

        return $instance;
    }

    /**
     * Gets the 'features.loader' service.
     *
     * @return Object A %features.loader.class% instance.
     */
    protected function getFeatures_LoaderService()
    {
        $class = $this->getParameter('features.loader.class');
        $instance = new $class($this->getParameter('features.path'), $this);

        return $instance;
    }

    /**
     * Gets the 'steps.loader' service.
     *
     * @return Object A %steps.loader.class% instance.
     */
    protected function getSteps_LoaderService()
    {
        $class = $this->getParameter('steps.loader.class');
        $instance = new $class($this->getParameter('steps.path'), $this->getEnvironmentService());

        return $instance;
    }

    /**
     * Returns service ids for a given annotation.
     *
     * @param string $name The annotation name
     *
     * @return array An array of annotations
     */
    public function findAnnotatedServiceIds($name)
    {
        static $annotations = array();

        return isset($annotations[$name]) ? $annotations[$name] : array();
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'parser.class'          => 'Everzet\\Gherkin\\Parser',
            'i18n.class'            => 'Everzet\\Gherkin\\I18n',
            'environment.class'     => 'Everzet\\Behat\\Environment\\WorldEnvironment',
            'features.loader.class' => 'Everzet\\Behat\\Loader\\FeaturesLoader',
            'steps.loader.class'    => 'Everzet\\Behat\\Loader\\StepsLoader',
            'logger.class'          => 'Everzet\\Behat\\Logger\\DetailedLogger',
        );
    }
}
