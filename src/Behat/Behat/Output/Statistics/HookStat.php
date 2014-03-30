<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

/**
 * Represents hook stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface HookStat
{
    /**
     * Returns hook name.
     *
     * @return string
     */
    public function getName();

    /**
     * Checks if hook was successful.
     *
     * @return Boolean
     */
    public function isSuccessful();
}
