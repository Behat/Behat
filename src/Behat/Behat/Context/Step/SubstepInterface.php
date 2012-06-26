<?php

namespace Behat\Behat\Context\Step;

use Behat\Gherkin\Node\StepNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Substep interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SubstepInterface
{
    /**
     * Returns substep node.
     *
     * @return StepNode
     */
    public function getStepNode();
}
