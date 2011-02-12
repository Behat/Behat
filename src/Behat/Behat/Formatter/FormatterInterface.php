<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\Translation\Translator;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Formatter interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface FormatterInterface
{
    /**
     * Set formatter translator.
     *
     * @param   Symfony\Component\Translation\Translator    $translator
     */
    function setTranslator(Translator $translator);

    /**
     * Checks if current formatter has parameter.
     *
     * @param   string  $name
     *
     * @return  boolean
     */
    function hasParameter($name);

    /**
     * Sets formatter parameter.
     *
     * @param   string  $name   parameter name
     * @param   mixed   $value  parameter value
     */
    function setParameter($name, $value);

    /**
     * Returns parameter value.
     *
     * @param   string  $name   parameter name
     *
     * @return  mixed
     */
    function getParameter($name);

    /**
     * Registers event listeners.
     *
     * WARNING: Always register listeners with lowest available priority (-10 as last argument to connect())
     *
     * @param   Behat\Behat\EventDispatcher\EventDispatcher $dispatcher
     */
    function registerListeners(EventDispatcher $dispatcher);
}
