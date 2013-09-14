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
use Behat\Behat\Event\FeatureEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\ScenarioTesterCarrierEvent;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Exception;
use RuntimeException;

/**
 * Feature tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTester extends DispatchingService
{
    /**
     * Tests feature.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param FeatureNode          $feature
     * @param Boolean              $skip
     *
     * @return integer
     */
    public function test(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        FeatureNode $feature,
        $skip = false
    )
    {
        $status = $skip ? StepEvent::SKIPPED : StepEvent::PASSED;

        $event = new FeatureEvent($suite, $contexts, $feature);
        $this->dispatch(EventInterface::BEFORE_FEATURE, $event);

        try {
            !$skip && $this->dispatch(EventInterface::HOOKABLE_BEFORE_FEATURE, $event);
        } catch (Exception $e) {
            $status = StepEvent::FAILED;
            $skip = true;
        }

        foreach ($feature->getScenarios() as $scenario) {
            $tester = $this->getScenarioTester($suite, $contexts, $scenario);
            $status = max($status, $tester->test($suite, $contexts, $scenario, $skip));
        }

        $event = new FeatureEvent($suite, $contexts, $feature, $status);

        try {
            !$skip && $this->dispatch(EventInterface::HOOKABLE_AFTER_FEATURE, $event);
        } catch (Exception $e) {
            $status = StepEvent::FAILED;
            $event = new FeatureEvent($suite, $contexts, $feature, $status);
        }

        $this->dispatch(EventInterface::AFTER_FEATURE, $event);

        return $status;
    }

    /**
     * Returns scenario tester.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param ScenarioInterface    $scenario
     *
     * @throws RuntimeException If scenario tester is not found
     *
     * @return ScenarioTester
     */
    private function getScenarioTester(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ScenarioInterface $scenario
    )
    {
        $testerProvider = new ScenarioTesterCarrierEvent($suite, $contexts, $scenario);

        $this->dispatch(EventInterface::CREATE_SCENARIO_TESTER, $testerProvider);
        if (!$testerProvider->hasTester()) {
            throw new RuntimeException(sprintf(
                'Can not find tester for "%s" scenario from the "%s" suite.',
                $scenario->getTitle(),
                $suite->getName()
            ));
        }

        return $testerProvider->getTester();
    }
}
