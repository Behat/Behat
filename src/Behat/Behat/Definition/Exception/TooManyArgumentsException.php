<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Exception;

use Behat\Behat\Definition\Definition;
use RuntimeException;

/**
 * Represents an exception caused by a pattern that captures more arguments than its definition accepts.
 */
final class TooManyArgumentsException extends RuntimeException implements SearchException
{
    public function __construct(Definition $definition, int $providedCount, int $parameterCount)
    {
        parent::__construct(sprintf(
            'The pattern "%s" provides %d argument%s but %s only accepts %d: '
            . 'either add the missing parameters or use non-capturing groups "(?:...)" in the pattern.',
            $definition->getPattern(),
            $providedCount,
            $providedCount === 1 ? '' : 's',
            $definition->getPath(),
            $parameterCount,
        ));
    }
}
