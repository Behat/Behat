<?php

namespace Behat\Behat\Formatter;

use Behat\Behat\Formatter\FormatterInterface;

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
 * Formatters manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FormatterManager
{
    private $translator;
    private $eventDispatcher;
    private $dispatchers = array();
    private $formatters  = array();

    /**
     * Initializes format manager.
     *
     * @param Translator      $translator
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(Translator $translator, EventDispatcher $eventDispatcher)
    {
        $this->translator      = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Adds formatter dispatcher to the manager.
     *
     * @param FormatterDispatcher $dispatcher Formatter dispatcher
     */
    public function addDispatcher(FormatterDispatcher $dispatcher)
    {
        $this->dispatchers[$dispatcher->getName()] = $dispatcher;
    }

    /**
     * Returns registered formatter dispatchers.
     *
     * @return array
     */
    public function getDispatchers()
    {
        return $this->dispatchers;
    }

    /**
     * Inits specific formatter class by format name.
     *
     * @param string $name
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function initFormatter($name)
    {
        if (isset($this->dispatchers[strtolower($name)])) {
            $dispatcher = $this->dispatchers[strtolower($name)];
        } elseif (class_exists($name)) {
            $dispatcher = new FormatterDispatcher($name);
        } else {
            throw new \RuntimeException("Unknown formatter: \"$name\". " .
                'Available formatters are: ' . implode(', ', array_keys($this->dispatchers))
            );
        }

        $formatter = $dispatcher->createFormatter();
        $formatter->setTranslator($this->translator);
        $this->eventDispatcher->addSubscriber($formatter, -5);

        return $this->formatters[] = $formatter;
    }

    /**
     * Sets specific parameter in all initialized formatters.
     *
     * @param string $param
     * @param mixed  $value
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
     * @return array
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * Disables formatter.
     *
     * @param FormatterInterface $formatter
     */
    public function disableFormatter(FormatterInterface $formatter)
    {
        $unsubscribe = array($this->eventDispatcher, 'removeSubscriber');
        $this->formatters = array_filter($this->formatters,
            function($registered) use($formatter, $unsubscribe) {
                if ($registered === $formatter) {
                    call_user_func($unsubscribe, $registered);

                    return false;
                }

                return true;
            }
        );
    }

    /**
     * Disables all initialized formatters.
     */
    public function disableFormatters()
    {
        foreach ($this->getFormatters() as $formatter) {
            $this->disableFormatter($formatter);
        }
    }
}
