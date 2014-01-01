<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Pattern;

/**
 * Pattern transformer interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface PatternTransformer
{
    /**
     * Transforms pattern string to regex.
     *
     * @param string $pattern
     *
     * @return string
     */
    public function toRegex($pattern);
}
