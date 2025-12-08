<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Stringable;

/**
 * Behat scenario stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ScenarioStat implements Stringable
{
    /**
     * @param TestResult::*|TestResults::NO_TESTS $resultCode
     */
    public function __construct(
        private ?string $title,
        private readonly string $path,
        private readonly int $resultCode,
    ) {
        $this->title = null;
    }

    /**
     * Returns scenario title.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Returns scenario path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns scenario result code.
     *
     * @return TestResult::*|TestResults::NO_TESTS
     */
    public function getResultCode(): int
    {
        return $this->resultCode;
    }

    /**
     * Returns string representation for a stat.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }
}
