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
use Behat\Behat\Event\BackgroundEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepContainerInterface;

/**
 * Background tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTester extends StepCollectionTester
{
    /**
     * Tests feature backgrounds.
     *
     * @param SuiteInterface         $suite
     * @param ScenarioInterface      $scenario
     * @param StepContainerInterface $container
     * @param BackgroundNode         $background
     * @param ContextPoolInterface   $contexts
     * @param Boolean                $skip
     *
     * @return integer
     */
    public function test(
        SuiteInterface $suite,
        ScenarioInterface $scenario,
        StepContainerInterface $container,
        BackgroundNode $background,
        ContextPoolInterface $contexts,
        $skip = false
    )
    {
        $status = $skip ? StepEvent::SKIPPED : StepEvent::PASSED;

        $event = new BackgroundEvent($suite, $contexts, $scenario, $container, $background);
        $this->dispatch(EventInterface::BEFORE_BACKGROUND, $event);

        foreach ($background->getSteps() as $step) {
            $skipStep = $skip || StepEvent::PASSED !== $status;

            $tester = $this->getStepTester($suite, $contexts, $step);
            $status = max($status, $tester->test($suite, $contexts, $scenario, $container, $step, $skipStep));
        }

        $event = new BackgroundEvent($suite, $contexts, $scenario, $container, $background, $status);
        $this->dispatch(EventInterface::AFTER_BACKGROUND, $event);

        return $status;
    }
}
