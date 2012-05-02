<?php

namespace Behat\Behat\Exception;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Pending exception (throw this to mark step as "pending").
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PendingException extends BehaviorException
{
    /**
     * Initializes pending exception.
     *
     * @param string $text TODO text
     */
    public function __construct($text = 'write pending definition')
    {
        parent::__construct(sprintf('TODO: %s', $text));
    }
}
