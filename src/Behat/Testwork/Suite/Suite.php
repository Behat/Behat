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
     */
    public function getName(): string;

    /**
     * Returns suite settings.
     */
    public function getSettings(): array;

    /**
     * Checks if a setting with provided name exists.
     */
    public function hasSetting(string $key): bool;

    /**
     * Returns setting value by its key.
     */
    public function getSetting(string $key): mixed;
}
