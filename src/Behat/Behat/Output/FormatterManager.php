<?php

namespace Behat\Behat\Output;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Output\Formatter\FormatterInterface;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Formatter manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FormatterManager
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var FormatterInterface[]
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
     * @param FormatterInterface $formatter
     */
    public function registerFormatter(FormatterInterface $formatter)
    {
        if (isset($this->formatters[$formatter->getName()])) {
            $this->disableFormatter($formatter->getName());
        }

        $this->formatters[$formatter->getName()] = $formatter;
    }

    /**
     * Returns all registered formatters.
     *
     * @return FormatterInterface[]
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * Checks if formatter with provided name exists.
     *
     * @param string $name
     *
     * @return Boolean
     */
    public function hasFormatter($name)
    {
        return isset($this->formatters[$name]);
    }

    /**
     * Returns formatter by name provided.
     *
     * @param string $name
     *
     * @return FormatterInterface
     *
     * @throws InvalidArgumentException If formatter with provided name doesn't exists
     */
    public function getFormatter($name)
    {
        if (!$this->hasFormatter($name)) {
            throw new InvalidArgumentException(sprintf(
                'Formatter "%s" not found.',
                $name
            ));
        }

        return $this->formatters[$name];
    }

    /**
     * Enable formatter by name provided.
     *
     * @param string $name
     */
    public function enableFormatter($name)
    {
        $this->eventDispatcher->addSubscriber($this->getFormatter($name));
    }

    /**
     * Disable formatter by name provided.
     *
     * @param string $name
     */
    public function disableFormatter($name)
    {
        $this->eventDispatcher->removeSubscriber($this->getFormatter($name));
    }

    /**
     * Disable all registered formatters.
     */
    public function disableAllFormatters()
    {
        array_map(array($this, 'disableFormatter'), array_keys($this->formatters));
    }

    /**
     * Sets specific formatter parameter value.
     *
     * @param string $formatterName
     * @param string $parameterName
     * @param mixed  $parameterValue
     */
    public function setFormatterParameter($formatterName, $parameterName, $parameterValue)
    {
        $this->getFormatter($formatterName)->setParameter($parameterName, $parameterValue);
    }

    /**
     * Sets specific formatter parameters.
     *
     * @param string $formatterName
     * @param array  $parameters
     */
    public function setFormatterParameters($formatterName, array $parameters)
    {
        foreach ($parameters as $key => $val) {
            $this->setFormatterParameter($formatterName, $key, $val);
        }
    }

    /**
     * Sets specific parameter to formatters that support it.
     *
     * @param string $parameterName
     * @param mixed  $parameterValue
     */
    public function setFormattersParameterIfExists($parameterName, $parameterValue)
    {
        foreach ($this->formatters as $formatter) {
            if ($formatter->hasParameter($parameterName)) {
                $formatter->setParameter($parameterName, $parameterValue);
            }
        }
    }
}
