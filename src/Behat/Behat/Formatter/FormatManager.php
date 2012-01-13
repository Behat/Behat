<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\Translation\Translator,
    Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Format manager.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FormatManager
{
    /**
     * Translator service.
     *
     * @var     Symfony\Component\Translation\Translator
     */
    private $translator;
    /**
     * Event dispatcher.
     *
     * @var     Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $dispatcher;
    /**
     * List of initialized formatters.
     *
     * @var     array
     */
    private $formatters = array();
    /**
     * Default formatters.
     */
    private static $defaultFormatterClasses = array(
        'pretty'   => 'Behat\Behat\Formatter\PrettyFormatter',
        'progress' => 'Behat\Behat\Formatter\ProgressFormatter',
        'html'     => 'Behat\Behat\Formatter\HtmlFormatter',
        'junit'    => 'Behat\Behat\Formatter\JUnitFormatter',
        'failed'   => 'Behat\Behat\Formatter\FailedScenariosFormatter',
        'snippets' => 'Behat\Behat\Formatter\SnippetsFormatter',
    );
    /**
     * Formatter classes.
     *
     * @var     array
     */
    private $formatterClasses = array();

    /**
     * Initializes format manager.
     *
     * @param   Symfony\Component\Translation\Translator            $translator
     * @param   Symfony\Component\EventDispatcher\EventDispatcher   $dispatcher
     */
    public function __construct(Translator $translator, EventDispatcher $dispatcher)
    {
        $this->translator = $translator;
        $this->dispatcher = $dispatcher;

        $this->formatterClasses = self::$defaultFormatterClasses;
    }

    /**
     * Returns default formatter names and their classes.
     *
     * @return  array
     */
    public static function getDefaultFormatterClasses()
    {
        return self::$defaultFormatterClasses;
    }

    /**
     * Returns all currently available formats with their classes.
     *
     * @return  array
     */
    public function getFormatterClasses()
    {
        return $this->formatterClasses;
    }

    /**
     * Sets formatter class to specific format.
     *
     * @param   string  $name   format name
     * @param   string  $class  formatter class
     */
    public function setFormatterClass($name, $class)
    {
        $name = strtolower($name);
        $this->formatterClasses[$name] = $class;
    }

    /**
     * Inits specific formatter class by format name.
     *
     * @param   string  $name
     */
    public function initFormatter($name)
    {
        $name = strtolower($name);

        if (class_exists($name)) {
            $class = $name;
        } elseif (isset($this->formatterClasses[$name])) {
            $class = $this->formatterClasses[$name];
        } else {
            throw new \RuntimeException("Unknown formatter: \"$name\". " .
                'Available formatters are: ' . implode(', ', array_keys($this->formatterClasses))
            );
        }

        $refClass = new \ReflectionClass($class);
        if (!$refClass->implementsInterface('Behat\Behat\Formatter\FormatterInterface')) {
            throw new \RuntimeException(sprintf(
                'Formatter class "%s" should implement FormatterInterface', $class
            ));
        }

        $formatter = new $class();
        $formatter->setTranslator($this->translator);
        $this->dispatcher->addSubscriber($formatter, -5);

        return $this->formatters[] = $formatter;
    }

    /**
     * Sets specific parameter in all initialized formatters.
     *
     * @param   string  $param  parameter name
     * @param   mixed   $value  parameter value
     */
    public function setFormattersParameter($param, $value)
    {
        foreach ($this->formatters as $formatter) {
            $formatter->setParameter($param, $value);
        }
    }

    /**
     * Returns all initialized formatters.
     *
     * @return  array
     */
    public function getFormatters()
    {
        return $this->formatters;
    }
}
