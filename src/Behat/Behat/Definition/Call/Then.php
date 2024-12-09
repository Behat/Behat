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
 * Then steps definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Then extends RuntimeDefinition
{
    public const KEYWORD = 'Then';

    /**
     * Initializes definition.
     *
     * @param array<object|string, string>|callable $callable
     */
    public function __construct(string $pattern, $callable, ?string $description = null)
    {
        parent::__construct(self::KEYWORD, $pattern, $callable, $description);
    }
}
