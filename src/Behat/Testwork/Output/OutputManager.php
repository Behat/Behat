<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output;

use Behat\Testwork\Output\Exception\FormatterNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manages formatters and their configuration.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class OutputManager
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var Formatter[]
     */
    private $formatters = array();

    /**
     * Initializes manager.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Registers formatter.
     *
     * @param Formatter $formatter
     */
    public function registerFormatter(Formatter $formatter)
    {
        if (isset($this->formatters[$formatter->getName()])) {
            $this->disableFormatter($formatter->getName());
        }

        $this->formatters[$formatter->getName()] = $formatter;
    }

    /**
     * Checks if formatter is registered.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isFormatterRegistered($name)
    {
        return isset($this->formatters[$name]);
    }

    /**
     * Returns formatter by name provided.
     *
     * @param string $name
     *
     * @return Formatter
     *
     * @throws FormatterNotFoundException
     */
    public function getFormatter($name)
    {
        if (!$this->isFormatterRegistered($name)) {
            throw new FormatterNotFoundException(sprintf(
                '`%s` formatter is not found or has not been properly registered. Registered formatters: `%s`.',
                $name,
                implode('`, `', array_keys($this->formatters))
            ), $name);
        }

        return $this->formatters[$name];
    }

    /**
     * Returns all registered formatters.
     *
     * @return Formatter[]
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * Enable formatter by name provided.
     *
     * @param string $formatter
     */
    public function enableFormatter($formatter)
    {
        if (!$this->isFormatterRegistered($formatter) && class_exists($formatter)) {
            $formatterInstance = new $formatter();
            $formatter = $formatterInstance->getName();

            if (!$this->isFormatterRegistered($formatter)) {
                $this->registerFormatter($formatterInstance);
            }
        }

        $this->eventDispatcher->addSubscriber($this->getFormatter($formatter));
    }

    /**
     * Disable formatter by name provided.
     *
     * @param string $formatter
     */
    public function disableFormatter($formatter)
    {
        $this->eventDispatcher->removeSubscriber($this->getFormatter($formatter));
    }

    /**
     * Disable all registered formatters.
     */
    public function disableAllFormatters()
    {
        array_map(array($this, 'disableFormatter'), array_keys($this->formatters));
    }

    /**
     * Sets provided parameter to said formatter.
     *
     * @param string $formatter
     * @param string $parameterName
     * @param mixed  $parameterValue
     */
    public function setFormatterParameter($formatter, $parameterName, $parameterValue)
    {
        $formatter = $this->getFormatter($formatter);
        $printer = $formatter->getOutputPrinter();

        switch ($parameterName) {
            case 'output_verbosity':
                $printer->setOutputVerbosity($parameterValue);

                return;
            case 'output_path':
                $printer->setOutputPath($parameterValue);

                return;
            case 'output_decorate':
                $printer->setOutputDecorated($parameterValue);

                return;
            case 'output_styles':
                $printer->setOutputStyles($parameterValue);

                return;
        }

        $formatter->setParameter($parameterName, $parameterValue);
    }

    /**
     * Sets provided formatter parameters.
     *
     * @param string $formatter
     * @param array  $parameters
     */
    public function setFormatterParameters($formatter, array $parameters)
    {
        foreach ($parameters as $key => $val) {
            $this->setFormatterParameter($formatter, $key, $val);
        }
    }

    /**
     * Sets provided parameter to all registered formatters.
     *
     * @param string $parameterName
     * @param mixed  $parameterValue
     */
    public function setFormattersParameter($parameterName, $parameterValue)
    {
        foreach (array_keys($this->formatters) as $formatter) {
            $this->setFormatterParameter($formatter, $parameterName, $parameterValue);
        }
    }

    /**
     * Sets provided parameters to all registered formatters.
     *
     * @param array $parameters
     */
    public function setFormattersParameters(array $parameters)
    {
        foreach ($parameters as $key => $val) {
            $this->setFormattersParameter($key, $val);
        }
    }
}
