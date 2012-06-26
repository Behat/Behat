<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventSubscriberInterface,
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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface FormatterInterface extends EventSubscriberInterface
{
    /**
     * Set formatter translator.
     *
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator);

    /**
     * Checks if current formatter has parameter.
     *
     * @param string $name
     *
     * @return Boolean
     */
    public function hasParameter($name);

    /**
     * Sets formatter parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParameter($name, $value);

    /**
     * Returns parameter value.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name);
}
