<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Statistics;

use Behat\Behat\Tester\Result\StepResult;
use Stringable;

/**
 * Second iteration of Behat step stat, with a scenario information.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepStatV2 extends StepStat implements Stringable
{
    /**
     * @param StepResult::* $resultCode
     */
    public function __construct(
        private readonly string $scenarioTitle,
        private readonly string $scenarioPath,
        private readonly string $stepText,
        private readonly string $stepPath,
        private readonly int $resultCode,
        private readonly ?string $error = null,
        private readonly ?string $stdOut = null,
    ) {
    }

    /**
     * Returns associated scenario text.
     */
    public function getScenarioText(): string
    {
        return $this->scenarioTitle;
    }

    /**
     * Returns associated scenario path.
     */
    public function getScenarioPath(): string
    {
        return $this->scenarioPath;
    }

    /**
     * Returns step text.
     */
    public function getStepText(): string
    {
        return $this->stepText;
    }

    /**
     * Returns step text.
     */
    public function getText(): string
    {
        return $this->stepText;
    }

    /**
     * Returns step path.
     */
    public function getStepPath(): string
    {
        return $this->stepPath;
    }

    /**
     * Returns step path.
     */
    public function getPath(): string
    {
        return $this->stepPath;
    }

    /**
     * Returns step result code.
     *
     * @return StepResult::*
     */
    public function getResultCode(): int
    {
        return $this->resultCode;
    }

    /**
     * Returns step error (if has one).
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Returns step output (if has one).
     */
    public function getStdOut(): ?string
    {
        return $this->stdOut;
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
