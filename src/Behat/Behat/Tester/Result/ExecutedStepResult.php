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
     * @var SearchResult
     */
    private $searchResult;
    /**
     * @var CallResult|null
     */
    private $callResult;

    /**
     * Initialize test result.
     */
    public function __construct(SearchResult $searchResult, CallResult $callResult)
    {
        $this->searchResult = $searchResult;
        $this->callResult = $callResult;
    }

    /**
     * Returns definition search result.
     *
     * @return SearchResult
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }

    /**
     * Returns definition call result or null if no call were made.
     *
     * @return CallResult
     */
    public function getCallResult()
    {
        return $this->callResult;
    }

    public function getStepDefinition()
    {
        return $this->searchResult->getMatchedDefinition();
    }

    public function hasException()
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
    public function getResultCode()
    {
        if ($this->callResult->hasException() && $this->callResult->getException() instanceof PendingException) {
            return self::PENDING;
        }

        if ($this->callResult->hasException()) {
            return self::FAILED;
        }

        return self::PASSED;
    }

    public function isPassed()
    {
        return self::PASSED == $this->getResultCode();
    }
}
