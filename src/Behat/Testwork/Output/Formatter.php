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
 *
 * @api
 */
interface Formatter extends EventSubscriberInterface
{
    /**
     * Returns formatter name.
     */
    public function getName(): string;

    /**
     * Returns formatter description.
     */
    public function getDescription(): string;

    /**
     * Returns formatter output printer.
     */
    public function getOutputPrinter(): OutputPrinter;

    /**
     * Sets formatter parameter.
     */
    public function setParameter(string $name, $value): void;

    /**
     * Returns parameter name.
     */
    public function getParameter(string $name): mixed;
}
