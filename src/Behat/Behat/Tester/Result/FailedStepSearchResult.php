<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

use Behat\Behat\Definition\Exception\SearchException;
use Behat\Testwork\Tester\Result\ExceptionResult;

/**
 * Represents a step test result with a failed definition search.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FailedStepSearchResult implements StepResult, ExceptionResult
{
    /**
     * Initializes result.
     */
    public function __construct(
        private readonly SearchException $searchException,
    ) {
    }

    public function hasException(): bool
    {
        return true;
    }

    public function getException(): SearchException
    {
        return $this->searchException;
    }

    public function isPassed(): bool
    {
        return false;
    }

    /**
     * @return self::FAILED
     */
    public function getResultCode(): int
    {
        return self::FAILED;
    }
}
