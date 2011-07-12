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
 * Then substep.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Then implements SubstepInterface
{
    /**
     * Step node.
     *
     * @var     Behat\Gherkin\Node\StepNode
     */
    private $node;

    /**
     * Initializes Then substep.
     */
    public function __construct()
    {
        $arguments = func_get_args();

        $text = array_shift($arguments);
        $node = new StepNode('Then', $text);
        $node->setArguments($arguments);

        $this->node = $node;
    }

    /**
     * @see     Behat\Behat\Context\Step\StepInterface::getStepNode()
     */
    public function getStepNode()
    {
        return $this->node;
    }
}
