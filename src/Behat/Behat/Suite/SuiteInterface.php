<?php

namespace Behat\Behat\Suite;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Basic suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SuiteInterface
{
    /**
     * Returns unique ID of this suite.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns suite name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns context class names.
     *
     * @return string[]
     */
    public function getContextClasses();

    /**
     * Returns parameters.
     *
     * @return array
     */
    public function getParameters();
}
