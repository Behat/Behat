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
     * Initializes step result.
     */
    public function __construct(
        private readonly SearchResult $searchResult,
    ) {
    }

    /**
     * Returns definition search result.
     */
    public function getSearchResult(): SearchResult
    {
        return $this->searchResult;
    }

    public function getStepDefinition()
    {
        return $this->searchResult->getMatchedDefinition();
    }

    public function isPassed(): bool
    {
        return false;
    }

    /**
     * @return self::SKIPPED
     */
    public function getResultCode(): int
    {
        return self::SKIPPED;
    }
}
