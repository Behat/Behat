<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Output\Node\Printer;

use Behat\Testwork\Call\CallResults;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;
use Behat\Testwork\Output\Formatter;

/**
 * Behat hook printer interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface HookPrinter
{
    /**
     * Prints hook call results.
     *
     * @param Formatter      $formatter
     * @param string         $hookedEventName
     * @param LifecycleEvent $hookedEvent
     * @param CallResults    $results
     */
    public function printHookResults(
        Formatter $formatter,
        $hookedEventName,
        LifecycleEvent $hookedEvent,
        CallResults $results
    );
}
