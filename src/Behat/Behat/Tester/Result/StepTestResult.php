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
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Testwork\Call\CallResult;
use Exception;

/**
 * Step test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepTestResult extends TestResult
{
    /**
     * @var SearchResult
     */
    private $searchResult;
    /**
     * @var SearchException
     */
    private $searchException;
    /**
     * @var null|CallResult
     */
    private $callResult;

    /**
     * Initialize test result.
     *
     * @param null|SearchResult    $searchResult
     * @param null|SearchException $searchException
     * @param null|CallResult      $callResult
     */
    public function __construct(
        SearchResult $searchResult = null,
        SearchException $searchException = null,
        CallResult $callResult = null
    ) {
        $this->searchResult = $searchResult;
        $this->searchException = $searchException;
        $this->callResult = $callResult;
    }

    /**
     * Returns definition search result.
     *
     * @return null|SearchResult
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }

    /**
     * Returns definition search exception (if has one).
     *
     * @return null|SearchException
     */
    public function getSearchException()
    {
        return $this->searchException;
    }

    /**
     * Returns definition call result or null if no call were made.
     *
     * @return null|CallResult
     */
    public function getCallResult()
    {
        return $this->callResult;
    }

    /**
     * Checks if definition search caused exception.
     *
     * @return Boolean
     */
    public function hasSearchException()
    {
        return null !== $this->searchException;
    }

    /**
     * Checks if step has produced any exception.
     *
     * @return Boolean
     */
    public function hasException()
    {
        return null !== $this->getException();
    }

    /**
     * @return Exception|null
     */
    public function getException()
    {
        if ($this->searchException) {
            return $this->searchException;
        }

        if ($this->callResult->hasException()) {
            return $this->callResult->getException();
        }

        return null;
    }

    /**
     * Checks if definition has been found.
     *
     * @return Boolean
     */
    public function hasFoundDefinition()
    {
        return $this->searchResult && $this->searchResult->hasMatch();
    }

    /**
     * Returns tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        if ($this->searchException) {
            return static::FAILED;
        }
        if (!$this->searchResult->hasMatch()) {
            return static::UNDEFINED;
        }
        if (null === $this->callResult) {
            return static::SKIPPED;
        }
        if ($this->callResult->getException() instanceof PendingException) {
            return static::PENDING;
        }
        if ($this->callResult->hasException()) {
            return static::FAILED;
        }

        return static::PASSED;
    }
}
