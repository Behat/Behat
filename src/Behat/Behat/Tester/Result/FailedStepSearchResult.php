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
use Throwable;

/**
 * Represents a step test result with a failed definition search.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FailedStepSearchResult implements StepResult, ExceptionResult
{
    /**
     * @var SearchException
     */
    private $searchException;

    /**
     * Initializes result.
     */
    public function __construct(SearchException $searchException)
    {
        $this->searchException = $searchException;
    }

    public function hasException()
    {
        return true;
    }

    /**
     * @return Throwable
     */
    public function getException()
    {
        return $this->searchException;
    }

    public function isPassed()
    {
        return false;
    }

    /**
     * @return self::FAILED
     */
    public function getResultCode()
    {
        return self::FAILED;
    }
}
