<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Config\Handler;

use Behat\Testwork\Tester\Result\Interpretation\StrictInterpretation;
use Behat\Testwork\Tester\Result\ResultInterpreter;

/**
 * Enables strict mode via config.
 */
final class StrictHandler
{
    public function __construct(
        private readonly ResultInterpreter $resultInterpreter
    ) {
    }

    public function registerStrictInterpretation()
    {
        $this->resultInterpreter->registerResultInterpretation(new StrictInterpretation());
    }
}
