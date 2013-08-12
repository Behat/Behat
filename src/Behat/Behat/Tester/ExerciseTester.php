<?php

namespace Behat\Behat\Tester;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\ExerciseEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Features\SuitedFeature;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\SuiteTesterCarrierEvent;
use RuntimeException;

/**
 * Exercise (set of suited features) tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExerciseTester extends DispatchingService
{
    /**
     * Tests suited features.
     *
     * @param SuitedFeature[] $suitedFeatures
     *
     * @return integer
     */
    public function test(array $suitedFeatures)
    {
        $event = new ExerciseEvent(false);
        $this->dispatch(EventInterface::BEFORE_EXERCISE, $event);

        $suites = array();
        $features = array();
        foreach ($suitedFeatures as $suitedFeature) {
            $suite = $suitedFeature->getSuite();
            $feature = $suitedFeature->getFeature();

            if (!isset($features[$suite->getId()])) {
                $features[$suite->getId()] = array();
            }

            $suites[$suite->getId()] = $suite;
            $features[$suite->getId()][] = $feature;
        }

        $result = 0;
        foreach ($suites as $id => $suite) {
            $tester = $this->getSuiteTester($suite);
            $result = max($result, $tester->test($suite, $features[$id]));
        }

        $event = new ExerciseEvent(true);
        $this->dispatch(EventInterface::AFTER_EXERCISE, $event);

        return $result;
    }

    /**
     * Returns suite tester.
     *
     * @param SuiteInterface $suite
     *
     * @throws RuntimeException If suite tester was not found
     *
     * @return SuiteTester
     */
    private function getSuiteTester(SuiteInterface $suite)
    {
        $testerProvider = new SuiteTesterCarrierEvent($suite);

        $this->dispatch(EventInterface::CREATE_SUITE_TESTER, $testerProvider);
        if (!$testerProvider->hasTester()) {
            throw new RuntimeException(sprintf(
                'Can not find tester for "%s" suite.', $suite->getName()
            ));
        }

        return $testerProvider->getTester();
    }
}
