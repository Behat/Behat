<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Call;

/**
 * Given steps definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Given extends RuntimeDefinition
{
    /**
     * Initializes definition.
     *
     * @param string      $pattern
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($pattern, $callable, $description = null)
    {
        parent::__construct('Given', $pattern, $callable, $description);
    }
}
