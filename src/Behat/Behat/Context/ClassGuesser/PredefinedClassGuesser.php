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

    /**
     * Initializes guesser.
     *
     * @param string $classname
     */
    public function __construct($classname)
    {
        $this->classname = $classname;
    }

    /**
     * Tries to guess context classname.
     *
     * @return string|null
     */
    public function guess()
    {
        return class_exists($this->classname) ? $this->classname : null;
    }
}
