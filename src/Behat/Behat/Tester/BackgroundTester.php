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
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ScenarioNode;

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
     * @param SuiteInterface       $suite
     * @param ScenarioNode         $scenario
     * @param BackgroundNode       $background
     * @param ContextPoolInterface $contexts
     *
     * @return integer
     */
    public function test(
        SuiteInterface $suite,
        ScenarioNode $scenario,
        BackgroundNode $background,
        ContextPoolInterface $contexts
    )
    {
        $event = new BackgroundEvent($suite, $contexts, $scenario, $background);
        $this->dispatch(EventInterface::BEFORE_BACKGROUND, $event);

        $result = 0;
        foreach ($background->getSteps() as $step) {
            $tester = $this->getStepTester($suite, $contexts, $step, $result);
            $result = max($result, $tester->test($suite, $contexts, $step, $scenario));
        }

        $event = new BackgroundEvent($suite, $contexts, $scenario, $background, $result);
        $this->dispatch(EventInterface::AFTER_BACKGROUND, $event);

        return $result;
    }
}
