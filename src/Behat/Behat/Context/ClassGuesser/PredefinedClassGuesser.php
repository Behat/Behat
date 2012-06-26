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
 * Predefined context class guesser.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PredefinedClassGuesser implements ClassGuesserInterface
{
    private $classname;
    private $force;

    /**
     * Initializes guesser.
     *
     * @param string  $classname
     * @param Boolean $force
     */
    public function __construct($classname, $force = false)
    {
        $this->classname = $classname;
        $this->force     = (bool) $force;
    }

    /**
     * Tries to guess context classname.
     *
     * @return string|null
     */
    public function guess()
    {
        return $this->force || class_exists($this->classname) ? $this->classname : null;
    }
}
