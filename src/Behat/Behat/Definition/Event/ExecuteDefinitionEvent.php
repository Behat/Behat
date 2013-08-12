<?php

namespace Behat\Behat\Definition\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;
use Behat\Behat\Callee\Event\ExecuteCalleeEvent;
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\StepNode;

/**
 * Definition execution event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExecuteDefinitionEvent extends ExecuteCalleeEvent
{
    /**
     * @var StepNode
     */
    private $step;

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param StepNode             $step
     * @param CalleeInterface      $callee
     * @param array                $arguments
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        StepNode $step,
        CalleeInterface $callee,
        array $arguments
    )
    {
        $this->step = $step;

        parent::__construct($suite, $contexts, $callee, $arguments);
    }

    /**
     * Returns step.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }
}
