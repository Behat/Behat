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

/**
 * Represents a skipped step result.
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
     */
    public function __construct(SearchResult $searchResult)
    {
        $this->searchResult = $searchResult;
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

    public function getStepDefinition()
    {
        return $this->searchResult->getMatchedDefinition();
    }

    public function isPassed()
    {
        return false;
    }

    /**
     * @return self::SKIPPED
     */
    public function getResultCode()
    {
        return self::SKIPPED;
    }
}
