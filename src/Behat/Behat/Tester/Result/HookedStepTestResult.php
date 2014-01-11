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
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Call\CallResults;

/**
 * Hooked step test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookedStepTestResult extends StepTestResult
{
    /**
     * @var CallResults
     */
    private $hookCallResults;

    /**
     * Initialize test result.
     *
     * @param null|SearchResult    $searchResult
     * @param null|SearchException $searchException
     * @param null|CallResult      $callResult
     * @param CallResults          $hookCallResults
     */
    public function __construct(
        SearchResult $searchResult = null,
        SearchException $searchException = null,
        CallResult $callResult = null,
        CallResults $hookCallResults
    ) {
        parent::__construct($searchResult, $searchException, $callResult);

        $this->hookCallResults = $hookCallResults;
    }

    /**
     * Returns hooks calls results.
     *
     * @return CallResults
     */
    public function getHookCallResults()
    {
        return $this->hookCallResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getException()
    {
        foreach ($this->hookCallResults as $callResult) {
            if ($callResult->hasException()) {
                return $callResult->getException();
            }
        }

        return parent::getException();
    }

    /**
     * {@inheritdoc}
     */
    public function getResultCode()
    {
        if ($this->hookCallResults->hasExceptions()) {
            return static::FAILED;
        }

        return parent::getResultCode();
    }
}
