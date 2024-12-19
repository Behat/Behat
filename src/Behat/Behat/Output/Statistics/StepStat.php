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

/**
 * Behat step stat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated in favour of StepStatV2 and to be removed in 4.0
 */
class StepStat
{
    /**
     * @param StepResult::* $resultCode
     */
    public function __construct(
        private string $text,
        private string $path,
        private int $resultCode,
        private ?string $error = null,
        private ?string $stdOut = null
    ) {
    }

    /**
     * Returns step text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns step path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
     * @return null|string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns step output (if has one).
     *
     * @return null|string
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
