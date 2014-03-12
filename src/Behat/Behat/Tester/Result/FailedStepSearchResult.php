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
use Exception;

/**
 * Behat failed step search result.
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
     *
     * @param SearchException $searchException
     */
    public function __construct(SearchException $searchException)
    {
        $this->searchException = $searchException;
    }

    /**
     * Checks that the test result has exception.
     *
     * @return Boolean
     */
    public function hasException()
    {
        return true;
    }

    /**
     * Returns exception that test result has.
     *
     * @return null|Exception
     */
    public function getException()
    {
        return $this->searchException;
    }

    /**
     * Checks that test has passed.
     *
     * @return Boolean
     */
    public function isPassed()
    {
        return false;
    }

    /**
     * Returns tester result code.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return self::FAILED;
    }
}
