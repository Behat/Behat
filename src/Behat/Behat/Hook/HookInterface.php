<?php

namespace Behat\Behat\Hook;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;

/**
 * Hook interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface HookInterface extends CalleeInterface
{
    /**
     * Returns hooked event type.
     *
     * @return string
     */
    public function getEventName();
}
