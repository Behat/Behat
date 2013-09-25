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
use Behat\Behat\Event\ExampleEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\ExampleNode;
use Exception;

/**
 * Outline example tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExampleTester extends IsolatedStepCollectionTester
{
    /**
     * Tests outline example.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param ExampleNode          $example
     * @param Boolean              $skip
     *
     * @return integer
     */
    public function test(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ExampleNode $example,
        $skip = false
    )
    {
        $status = $skip ? StepEvent::SKIPPED : StepEvent::PASSED;

        $contexts = $this->initializeContextPool($suite, $contexts);

        $event = new ExampleEvent($suite, $contexts, $example);
        $this->dispatch(EventInterface::BEFORE_EXAMPLE, $event);

        try {
            !$skip && $this->dispatch(EventInterface::HOOKABLE_BEFORE_SCENARIO, $event);
        } catch (Exception $e) {
            $status = StepEvent::FAILED;
            $skip = true;
        }

        $outline = $example->getOutline();

        if ($example->getOutline()->getFeature()->hasBackground()) {
            $skipStep = $skip || StepEvent::PASSED !== $status;
            $background = $outline->getFeature()->getBackground();

            $tester = $this->getBackgroundTester($suite, $contexts, $background);
            $status = $tester->test($suite, $outline, $example, $background, $contexts, $skipStep);
        }

        foreach ($example->getSteps() as $step) {
            $skipStep = $skip || StepEvent::PASSED !== $status;

            $tester = $this->getStepTester($suite, $contexts, $step);
            $status = max($status, $tester->test($suite, $contexts, $outline, $example, $step, $skipStep));
        }

        $event = new ExampleEvent($suite, $contexts, $example, $status);

        try {
            !$skip && $this->dispatch(EventInterface::HOOKABLE_AFTER_SCENARIO, $event);
        } catch (Exception $e) {
            $status = StepEvent::FAILED;
            $event = new ExampleEvent($suite, $contexts, $example, $status);
        }

        $this->dispatch(EventInterface::AFTER_EXAMPLE, $event);

        return $status;
    }
}
