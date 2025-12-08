<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Tester\Result\ExceptionResult;

/**
 * Represents an executed (successfully or not) step result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ExecutedStepResult implements StepResult, DefinedStepResult, ExceptionResult
{
    /**
     * Initialize test result.
     */
    public function __construct(
        private readonly SearchResult $searchResult,
        private readonly CallResult $callResult,
    ) {
    }

    /**
     * Returns definition search result.
     */
    public function getSearchResult(): SearchResult
    {
        return $this->searchResult;
    }

    /**
     * Returns definition call result or null if no call were made.
     */
    public function getCallResult(): CallResult
    {
        return $this->callResult;
    }

    public function getStepDefinition()
    {
        return $this->searchResult->getMatchedDefinition();
    }

    public function hasException(): bool
    {
        return null !== $this->getException();
    }

    public function getException()
    {
        return $this->callResult->getException();
    }

    /**
     * @return self::PENDING|self::FAILED|self::PASSED
     */
    public function getResultCode(): int
    {
        if ($this->callResult->hasException() && $this->callResult->getException() instanceof PendingException) {
            return self::PENDING;
        }

        if ($this->callResult->hasException()) {
            return self::FAILED;
        }

        return self::PASSED;
    }

    public function isPassed(): bool
    {
        return self::PASSED == $this->getResultCode();
    }
}
