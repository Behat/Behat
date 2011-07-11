<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
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
interface FormatterInterface extends EventSubscriberInterface
{
    /**
     * Returns formatter description (printed in Behat help).
     *
     * @return  string
     */
    static function getDescription();

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
}
