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
     * Initialize formatter.
     *
     * @param   Translator  $translator
     */
    function __construct(Translator $translator);

    /**
     * Set parameter value.
     *
     * @param   string  $name   parameter name
     * @param   mixed   $value  parameter value
     */
    function setParameter($name, $value);

    /**
     * Return parameter value.
     *
     * @param   string  $name   parameter name
     * 
     * @return  mixed
     */
    function getParameter($name);

    /**
     * Register event listeners.
     *
     * @param   EventDispatcher $dispatcher
     */
    function registerListeners(EventDispatcher $dispatcher);
}
