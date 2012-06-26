<?php

namespace Behat\Behat\Hook;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Event\EventInterface,
    Behat\Behat\Annotation\AnnotationInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Hook interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface HookInterface
{
    /**
     * Returns hooked event type.
     *
     * @return string
     */
    public function getEventName();

    /**
     * Runs hook callback.
     *
     * @param EventInterface $event
     */
    public function run(EventInterface $event);
}
