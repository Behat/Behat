<?php

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\DependencyInjection\Container;
use Symfony\Components\DependencyInjection\Reference;
use Symfony\Components\DependencyInjection\Parameter;
use Symfony\Components\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Container
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class Container extends Container
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
     * Gets the 'world' service.
     *
     * @return Object A %world.class% instance.
     */
    protected function getWorldService()
    {
        $class = $this->getParameter('world.class');
        $instance = new $class($this->getParameter('world.file'));

        return $instance;
    }

    /**
     * Gets the 'logger.loader' service.
     *
     * @return Object A %logger.loader.class% instance.
     */
    protected function getLogger_LoaderService()
    {
        $class = $this->getParameter('logger.loader.class');
        $instance = new $class();

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
        $instance = new $class($this->getParameter('features.path'), $this->getParameter('container'));

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
        $instance = new $class($this->getParameter('steps.loader.path'), $this->getWorldService());

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
        static $annotations = array (
);

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
            'parser.class' => 'Everzet\\Gherkin\\Parser',
            'i18n.class' => 'Everzet\\Gherkin\\I18n',
            'world.class' => 'Everzet\\Behat\\Environment\\SimpleWorld',
            'features.loader.class' => 'Everzet\\Behat\\Loaders\\FeaturesLoader',
            'steps.loader.class' => 'Everzet\\Behat\\Loaders\\StepsLoader',
            'logger.loader.class' => 'Everzet\\Behat\\Loggers\\Detailed\\Loader',
        );
    }
}
