<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite;

/**
 * Represents a Testwork suite. Suite is a collection of tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Suite
{
    /**
     * Returns unique suite name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns suite settings.
     *
     * @return array
     */
    public function getSettings();

    /**
     * Checks if a setting with provided name exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasSetting($key);

    /**
     * Returns setting value by its key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSetting($key);
}
