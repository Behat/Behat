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
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\ScenarioTesterCarrierEvent;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;
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
     *
     * @return integer
     */
    public function test(SuiteInterface $suite, ContextPoolInterface $contexts, FeatureNode $feature)
    {
        $event = new FeatureEvent($suite, $contexts, $feature);
        $this->dispatch(EventInterface::BEFORE_FEATURE, $event);

        $result = 0;
        foreach ($feature->getScenarios() as $scenario) {
            $tester = $this->getScenarioTester($suite, $contexts, $scenario);
            $result = max($result, $tester->test($suite, $contexts, $scenario));
        }

        $event = new FeatureEvent($suite, $contexts, $feature, $result);
        $this->dispatch(EventInterface::AFTER_FEATURE, $event);

        return $result;
    }

    /**
     * Returns scenario tester.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param ScenarioNode         $scenario
     *
     * @throws RuntimeException If scenario tester is not found
     *
     * @return ScenarioTester
     */
    private function getScenarioTester(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ScenarioNode $scenario
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
