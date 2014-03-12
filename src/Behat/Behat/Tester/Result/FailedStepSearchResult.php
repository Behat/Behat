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
     * {@inheritdoc}
     */
    public function hasException()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getException()
    {
        return $this->searchException;
    }

    /**
     * {@inheritdoc}
     */
    public function isPassed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getResultCode()
    {
        return self::FAILED;
    }
}
