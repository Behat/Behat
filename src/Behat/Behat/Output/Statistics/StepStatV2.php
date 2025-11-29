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
        parent::__construct($stepText, $stepPath, $resultCode, $error, $stdOut);
    }

    /**
     * Returns associated scenario text.
     *
     * @return string
     */
    public function getScenarioText()
    {
        return $this->scenarioTitle;
    }

    /**
     * Returns associated scenario path.
     *
     * @return string
     */
    public function getScenarioPath()
    {
        return $this->scenarioPath;
    }

    /**
     * Returns step text.
     *
     * @return string
     */
    public function getStepText()
    {
        return $this->stepText;
    }

    /**
     * Returns step path.
     *
     * @return string
     */
    public function getStepPath()
    {
        return $this->stepPath;
    }

    /**
     * Returns step result code.
     *
     * @return StepResult::*
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * Returns step error (if has one).
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns step output (if has one).
     *
     * @return string|null
     */
    public function getStdOut()
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
