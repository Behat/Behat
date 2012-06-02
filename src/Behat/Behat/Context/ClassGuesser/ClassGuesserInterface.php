<?php

namespace Behat\Behat\Context\ClassGuesser;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context class guesser interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ClassGuesserInterface
{
    /**
     * Tries to guess context classname.
     *
     * @return string|null
     */
    public function guess();
}
