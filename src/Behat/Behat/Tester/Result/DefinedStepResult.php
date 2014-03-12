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

/**
 * Represents a step result that contains step definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface DefinedStepResult extends StepResult
{
    /**
     * Returns found step definition.
     *
     * @return null|Definition
     */
    public function getStepDefinition();
}
