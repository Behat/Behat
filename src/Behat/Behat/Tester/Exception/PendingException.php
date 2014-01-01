<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Exception;

use Behat\Testwork\Exception\TestworkException;
use RuntimeException;

/**
 * Pending exception (throw this to mark step as "pending").
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PendingException extends RuntimeException implements TestworkException
{
    /**
     * Initializes pending exception.
     *
     * @param string $text
     */
    public function __construct($text = 'write pending definition')
    {
        parent::__construct(sprintf('TODO: %s', $text));
    }
}
