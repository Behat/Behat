<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

/**
 * Represents an undefined step result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UndefinedStepResult implements StepResult
{
    public function isPassed(): bool
    {
        return false;
    }

    /**
     * @return self::UNDEFINED
     */
    public function getResultCode(): int
    {
        return self::UNDEFINED;
    }
}
