<?php

namespace Behat\Behat\Event;

use Behat\Behat\Context\ContextInterface;

use Behat\Gherkin\Node\OutlineNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Outline example event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineExampleEvent extends BaseScenarioEvent
{
    private $outline;
    private $iteration;

    /**
     * Initializes outline example event.
     *
     * @param OutlineNode      $outline
     * @param integer          $iteration iteration number
     * @param ContextInterface $context
     * @param integer          $result
     * @param Boolean          $skipped
     */
    public function __construct(OutlineNode $outline, $iteration, ContextInterface $context,
                                $result = null, $skipped = false)
    {
        parent::__construct($context, $result, $skipped);

        $this->outline   = $outline;
        $this->iteration = $iteration;
    }

    /**
     * Returns outline node.
     *
     * @return OutlineNode
     */
    public function getOutline()
    {
        return $this->outline;
    }

    /**
     * Returns example number on which event occurs.
     *
     * @return integer
     */
    public function getIteration()
    {
        return $this->iteration;
    }
}
