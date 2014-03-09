<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\SearchResult;

/**
 * Behat undefined step result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SkippedStepResult implements StepResult, DefinedStepResult
{
    /**
     * @var SearchResult
     */
    private $searchResult;

    /**
     * Initializes step result.
     *
     * @param SearchResult $searchResult
     */
    public function __construct(SearchResult $searchResult)
    {
        $this->searchResult = $searchResult;
    }

    /**
     * Returns found step definition.
     *
     * @return null|Definition
     */
    public function getStepDefinition()
    {
        return $this->searchResult->getMatchedDefinition();
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
        return self::SKIPPED;
    }
}
