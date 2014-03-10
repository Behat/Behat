<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output;

use Behat\Testwork\Output\Printer\OutputPrinter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Represents Testwork output formatter.
 *
 * @see OutputManager
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Formatter extends EventSubscriberInterface
{
    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns formatter output printer.
     *
     * @return OutputPrinter
     */
    public function getOutputPrinter();

    /**
     * Sets formatter parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParameter($name, $value);

    /**
     * Returns parameter name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name);
}
