<?php

namespace Behat\Behat\Tester;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Exception\UndefinedException;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;

/**
 * Step DispatchingTester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepSkipTester extends StepTester
{
    /**
     * Skips step testing.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param StepNode             $step
     * @param ScenarioNode         $scenario
     *
     * @return integer
     */
    public function test(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        StepNode $step,
        ScenarioNode $scenario
    )
    {
        $status = StepEvent::SKIPPED;

        $event = new StepEvent($suite, $contexts, $scenario, $step);
        $this->dispatch(EventInterface::BEFORE_STEP, $event);

        $execution = $exception = $snippet = null;

        try {
            $execution = $this->getExecutionEvent($suite, $contexts, $step);
        } catch (UndefinedException $e) {
            $status = StepEvent::UNDEFINED;
            $exception = $e;
            $snippet = $this->getDefinitionSnippet($suite, $contexts, $step);
        }

        $stdOut = $execution ? $execution->getStdOut() : null;
        $definition = $execution ? $execution->getCallee() : null;

        $event = new StepEvent(
            $suite, $contexts, $scenario, $step, $status, $stdOut, $exception, $definition, $snippet
        );
        $this->dispatch(EventInterface::AFTER_STEP, $event);

        return $status;
    }
}
