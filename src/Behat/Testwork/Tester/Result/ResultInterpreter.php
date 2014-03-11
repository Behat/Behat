<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

use Behat\Testwork\Tester\Result\Interpretation\ResultInterpretation;

/**
 * Interprets provided test result (as 1 or 0) using registered interpretations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ResultInterpreter
{
    /**
     * @var ResultInterpretation[]
     */
    private $interpretations = array();

    /**
     * Registers result interpretation.
     *
     * @param ResultInterpretation $interpretation
     */
    public function registerResultInterpretation(ResultInterpretation $interpretation)
    {
        $this->interpretations[] = $interpretation;
    }

    /**
     * Interprets result as a UNIX return code (0 for success, 1 for failure).
     *
     * @param TestResult $result
     *
     * @return integer
     */
    public function interpretResult(TestResult $result)
    {
        foreach ($this->interpretations as $interpretation) {
            if ($interpretation->isFailure($result)) {
                return 1;
            }
        }

        return 0;
    }
}
